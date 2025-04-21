<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyCircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Hook;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;
use function basename;

/**
 * @internal
 */
#[CoversClass(Processor::class)]
#[CoversClass(Executor::class)]
#[CoversClass(Iterator::class)]
#[CoversClass(Resolver::class)]
final class ProcessorTest extends TestCase {
    public function testRun(): void {
        $input  = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $events = [];

        $mock = Mockery::mock(Task::class);
        $mock
            ->shouldReceive('getExtensions')
            ->twice()
            ->andReturns(['php']);

        $taskA = new class() extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['htm'];
            }
        };
        $taskB = new ProcessorTest__Task([
            'a.txt'  => [
                '../b/b/bb.txt',
                '../c.txt',
                '../c.html',
                'excluded.txt',
            ],
            'bb.txt' => [
                '../../b/a/ba.txt',
                '../../c.txt',
                '../../../../../README.md',
            ],
        ]);
        $taskC = new class() extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return [Hook::Before->value];
            }
        };
        $taskD = new class() extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['htm'];
            }

            #[Override]
            public function __invoke(DependencyResolver $resolver, File $file): void {
                parent::__invoke($resolver, $file);

                $resolver->queue(
                    new FileReference('../'.basename(__FILE__)),
                );
            }
        };

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->consistent()
            ->addTask($mock)
            ->addTask($taskA)
            ->addTask($taskB)
            ->addTask($taskC)
            ->addTask($taskD)
            ->exclude(['excluded.txt', '**/**/excluded.txt'])
            ->addListener(
                static function (Event $event) use (&$events): void {
                    $events[] = $event;
                },
            )
            ->run(
                $input,
            );

        self::assertEquals(
            [
                new ProcessingStarted(),
                new FileStarted('@ :before'),
                new TaskStarted($taskC::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ a/a.txt'),
                new TaskStarted($taskB::class),
                new DependencyResolved('↔ b/b/bb.txt', DependencyResolvedResult::Success),
                new FileStarted('↔ b/b/bb.txt'),
                new TaskStarted($taskB::class),
                new DependencyResolved('↔ b/a/ba.txt', DependencyResolvedResult::Success),
                new FileStarted('↔ b/a/ba.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new DependencyResolved('↔ c.txt', DependencyResolvedResult::Success),
                new FileStarted('↔ c.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new DependencyResolved(
                    '! '.$input->getFilePath('../../../README.md'),
                    DependencyResolvedResult::Success,
                ),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new DependencyResolved('↔ c.txt', DependencyResolvedResult::Success),
                new DependencyResolved('↔ c.html', DependencyResolvedResult::Success),
                new DependencyResolved('↔ a/excluded.txt', DependencyResolvedResult::Success),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ a/a/aa.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ a/b/ab.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ b/b.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ c.htm'),
                new TaskStarted($taskA::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskD::class),
                new DependencyResolved(
                    '! '.$input->getFilePath('../ProcessorTest.php'),
                    DependencyResolvedResult::Queued,
                ),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('! '.$input->getFilePath('../ProcessorTest.php')),
                new FileFinished(FileFinishedResult::Skipped),
                new FileStarted('@ :after'),
                new FileFinished(FileFinishedResult::Skipped),
                new ProcessingFinished(ProcessingFinishedResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->getFilePath('c.htm'),
                    [],
                ],
            ],
            $taskA->processed,
        );
        self::assertEquals(
            [
                [
                    (string) $input->getFilePath('b/a/ba.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('c.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('b/b/bb.txt'),
                    [
                        '../../b/a/ba.txt'         => (string) $input->getFilePath('b/a/ba.txt'),
                        '../../c.txt'              => (string) $input->getFilePath('c.txt'),
                        '../../../../../README.md' => (string) $input->getFilePath('../../../README.md'),
                    ],
                ],
                [
                    (string) $input->getFilePath('a/a.txt'),
                    [
                        '../b/b/bb.txt' => (string) $input->getFilePath('b/b/bb.txt'),
                        '../c.txt'      => (string) $input->getFilePath('c.txt'),
                        '../c.html'     => (string) $input->getFilePath('c.html'),
                        'excluded.txt'  => (string) $input->getFilePath('a/excluded.txt'),
                    ],
                ],
                [
                    (string) $input->getFilePath('a/a/aa.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('a/b/ab.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('b/b.txt'),
                    [],
                ],
            ],
            $taskB->processed,
        );
    }

    public function testRunFile(): void {
        $task   = new ProcessorTest__Task();
        $input  = (new FilePath(self::getTestData()->path('excluded.txt')))->getNormalizedPath();
        $events = [];

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->addTask($task)
            ->addListener(
                static function (Event $event) use (&$events): void {
                    $events[] = $event;
                },
            )
            ->run(
                $input,
            );

        self::assertEquals(
            [
                new ProcessingStarted(),
                new FileStarted('@ :before'),
                new FileFinished(FileFinishedResult::Skipped),
                new FileStarted('↔ excluded.txt'),
                new TaskStarted(ProcessorTest__Task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('@ :after'),
                new FileFinished(FileFinishedResult::Skipped),
                new ProcessingFinished(ProcessingFinishedResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->getFilePath('excluded.txt'),
                    [
                        // empty
                    ],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunEach(): void {
        $taskA  = new ProcessorTest__Task();
        $taskB  = new class() extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return [Hook::Each->value];
            }
        };
        $taskC  = new class() extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return [Hook::Each->value];
            }
        };
        $input  = (new FilePath(self::getTestData()->path('excluded.txt')))->getNormalizedPath();
        $events = [];

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->addTask($taskA)
            ->addTask($taskB)
            ->addTask($taskC, -1)
            ->addListener(
                static function (Event $event) use (&$events): void {
                    $events[] = $event;
                },
            )
            ->run(
                $input,
            );

        self::assertEquals(
            [
                new ProcessingStarted(),
                new FileStarted('@ :before'),
                new FileFinished(FileFinishedResult::Skipped),
                new FileStarted('↔ excluded.txt'),
                new TaskStarted($taskC::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskA::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('@ :after'),
                new FileFinished(FileFinishedResult::Skipped),
                new ProcessingFinished(ProcessingFinishedResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->getFilePath('excluded.txt'),
                    [
                        // empty
                    ],
                ],
            ],
            $taskA->processed,
        );
    }

    public function testRunWildcard(): void {
        $input  = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $events = [];
        $taskA  = new class([
            'b.html' => [
                '../../../../README.md',
                '../a/excluded.txt',
            ],
        ]) extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['html'];
            }
        };
        $taskB  = new class() extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['*'];
            }
        };

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->consistent()
            ->addTask($taskA)
            ->addTask($taskB)
            ->exclude(['excluded.txt', '**/**/excluded.txt'])
            ->addListener(
                static function (Event $event) use (&$events): void {
                    $events[] = $event;
                },
            )
            ->run(
                $input,
            );

        self::assertEquals(
            [
                new ProcessingStarted(),
                new FileStarted('@ :before'),
                new FileFinished(FileFinishedResult::Skipped),
                new FileStarted('↔ a/a.html'),
                new TaskStarted($taskA::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ a/a.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ a/a/aa.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ a/b/ab.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ b/a/ba.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ b/b.html'),
                new TaskStarted($taskA::class),
                new DependencyResolved(
                    '! '.$input->getFilePath('../../../README.md'),
                    DependencyResolvedResult::Success,
                ),
                new DependencyResolved('↔ a/excluded.txt', DependencyResolvedResult::Success),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ b/b.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ b/b/bb.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ c.htm'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ c.html'),
                new TaskStarted($taskA::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('↔ c.txt'),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('@ :after'),
                new FileFinished(FileFinishedResult::Skipped),
                new ProcessingFinished(ProcessingFinishedResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->getFilePath('a/a.html'),
                    [],
                ],
                [
                    (string) $input->getFilePath('b/b.html'),
                    [
                        '../../../../README.md' => (string) $input->getFilePath('../../../README.md'),
                        '../a/excluded.txt'     => (string) $input->getFilePath('a/excluded.txt'),
                    ],
                ],
                [
                    (string) $input->getFilePath('c.html'),
                    [],
                ],
            ],
            $taskA->processed,
        );
        self::assertEquals(
            [
                [
                    (string) $input->getFilePath('a/a.html'),
                    [],
                ],
                [
                    (string) $input->getFilePath('a/a.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('a/a/aa.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('a/b/ab.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('b/a/ba.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('b/b.html'),
                    [],
                ],
                [
                    (string) $input->getFilePath('b/b.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('b/b/bb.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('c.htm'),
                    [],
                ],
                [
                    (string) $input->getFilePath('c.html'),
                    [],
                ],
                [
                    (string) $input->getFilePath('c.txt'),
                    [],
                ],
            ],
            $taskB->processed,
        );
    }

    public function testRunOutputInsideInput(): void {
        $input  = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $output = $input->getDirectoryPath('a');
        $events = [];
        $task   = new ProcessorTest__Task([
            'ba.txt' => [
                '../../a/a.txt',
            ],
        ]);

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->consistent()
            ->addTask($task)
            ->exclude(['excluded.txt', '**/**/excluded.txt'])
            ->addListener(
                static function (Event $event) use (&$events): void {
                    $events[] = $event;
                },
            )
            ->run(
                $input,
                $output,
            );

        self::assertEquals(
            [
                new ProcessingStarted(),
                new FileStarted('@ :before'),
                new FileFinished(FileFinishedResult::Skipped),
                new FileStarted('→ b/a/ba.txt'),
                new TaskStarted($task::class),
                new DependencyResolved('← a.txt', DependencyResolvedResult::Success),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('→ b/b.txt'),
                new TaskStarted($task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('→ b/b/bb.txt'),
                new TaskStarted($task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('→ c.txt'),
                new TaskStarted($task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('@ :after'),
                new FileFinished(FileFinishedResult::Skipped),
                new ProcessingFinished(ProcessingFinishedResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->getFilePath('b/a/ba.txt'),
                    [
                        '../../a/a.txt' => (string) $input->getFilePath('a/a.txt'),
                    ],
                ],
                [
                    (string) $input->getFilePath('b/b.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('b/b/bb.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('c.txt'),
                    [],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunFileNotFound(): void {
        $input = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task  = new ProcessorTest__Task(['*' => ['404.html']]);

        self::expectException(DependencyUnresolvable::class);
        self::expectExceptionMessage('Dependency not found.');

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->addTask($task)
            ->run($input);
    }

    public function testRunCircularDependency(): void {
        $input = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task  = new ProcessorTest__Task([
            'a.txt'  => ['../b/b.txt'],
            'b.txt'  => ['../b/a/ba.txt'],
            'ba.txt' => ['../../c.txt'],
            'c.txt'  => ['a/a.txt'],
        ]);

        self::expectException(DependencyCircularDependency::class);
        self::expectExceptionMessage(
            <<<MESSAGE
            Circular Dependency detected:

            * {$input}/a/a.txt
            * {$input}/b/b.txt
            * {$input}/b/a/ba.txt
            * {$input}/c.txt
            ! {$input}/a/a.txt
            MESSAGE,
        );

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->addTask($task)
            ->run($input);
    }

    public function testRunCircularDependencySelf(): void {
        $input = (new DirectoryPath(self::getTestData()->path('a/a')))->getNormalizedPath();
        $task  = new ProcessorTest__Task([
            'aa.txt' => ['aa.txt'],
        ]);

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->addTask($task)
            ->run($input);

        self::assertEquals(
            [
                [
                    (string) $input->getFilePath('aa.txt'),
                    [
                        'aa.txt' => (string) $input->getFilePath('aa.txt'),
                    ],
                ],
                [
                    (string) $input->getFilePath('excluded.txt'),
                    [
                        // empty
                    ],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunCircularDependencySelfThrough(): void {
        $input = (new DirectoryPath(self::getTestData()->path('a/a')))->getNormalizedPath();
        $task  = new ProcessorTest__Task([
            'aa.txt'       => ['excluded.txt'],
            'excluded.txt' => ['aa.txt'],
        ]);

        self::expectException(DependencyCircularDependency::class);
        self::expectExceptionMessage(
            <<<MESSAGE
            Circular Dependency detected:

            * {$input}/aa.txt
            * {$input}/excluded.txt
            ! {$input}/aa.txt
            MESSAGE,
        );

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->addTask($task)
            ->run($input);
    }

    public function testRunCircularDependencyNotWritable(): void {
        $events = [];
        $output = (new DirectoryPath(self::getTestData()->path('b')))->getNormalizedPath();
        $input  = (new DirectoryPath(self::getTestData()->path('a')))->getNormalizedPath();
        $task   = new ProcessorTest__Task([
            'aa.txt' => ['../a.txt'],
        ]);

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->consistent()
            ->addTask($task)
            ->exclude(['excluded.txt', '**/**/excluded.txt'])
            ->addListener(
                static function (Event $event) use (&$events): void {
                    $events[] = $event;
                },
            )
            ->run($input, $output);

        self::assertEquals(
            [
                new ProcessingStarted(),
                new FileStarted('@ :before'),
                new FileFinished(FileFinishedResult::Skipped),
                new FileStarted('→ a.txt'),
                new TaskStarted($task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('→ a/aa.txt'),
                new TaskStarted($task::class),
                new DependencyResolved('→ a.txt', DependencyResolvedResult::Success),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('→ b/ab.txt'),
                new TaskStarted($task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('@ :after'),
                new FileFinished(FileFinishedResult::Skipped),
                new ProcessingFinished(ProcessingFinishedResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->getFilePath('a.txt'),
                    [],
                ],
                [
                    (string) $input->getFilePath('a/aa.txt'),
                    [
                        '../a.txt' => (string) $input->getFilePath('a.txt'),
                    ],
                ],
                [
                    (string) $input->getFilePath('b/ab.txt'),
                    [],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunHookBefore(): void {
        $events = [];
        $input  = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task   = new class([
            '*' => [
                'excluded.txt',
            ],
        ]) extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return [Hook::Before->value];
            }
        };

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->addTask($task)
            ->exclude(['excluded.txt', '**/**/excluded.txt'])
            ->addListener(
                static function (Event $event) use (&$events): void {
                    $events[] = $event;
                },
            )
            ->run(
                $input,
            );

        self::assertEquals(
            [
                new ProcessingStarted(),
                new FileStarted('@ :before'),
                new TaskStarted($task::class),
                new DependencyResolved('↔ excluded.txt', DependencyResolvedResult::Success),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted('@ :after'),
                new FileFinished(FileFinishedResult::Skipped),
                new ProcessingFinished(ProcessingFinishedResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->getFilePath('@.'.Hook::Before->value),
                    [
                        'excluded.txt' => (string) $input->getFilePath('excluded.txt'),
                    ],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunHookBeforeNotSkippedDependency(): void {
        $events = [];
        $input  = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task   = new class([
            '*' => [
                'c.txt',
            ],
        ]) extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return [Hook::Before->value];
            }
        };

        self::expectException(DependencyUnavailable::class);

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->addTask($task)
            ->addTask(ProcessorTest__Task::class)
            ->exclude(['excluded.txt', '**/**/excluded.txt'])
            ->addListener(
                static function (Event $event) use (&$events): void {
                    $events[] = $event;
                },
            )
            ->run(
                $input,
            );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProcessorTest__Task implements Task {
    /**
     * @var array<array-key, array{string, array<string, mixed>}>
     */
    public array $processed = [];

    public function __construct(
        /**
         * @var array<string, list<string>>
         */
        private readonly array $dependencies = [],
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['txt', 'md'];
    }

    #[Override]
    public function __invoke(DependencyResolver $resolver, File $file): void {
        $resolved     = [];
        $dependencies = $this->dependencies[$file->getName()] ?? $this->dependencies['*'] ?? [];

        foreach ($dependencies as $dependency) {
            $resolved[$dependency] = $resolver->resolve(new FileReference($file->getFilePath($dependency)));
        }

        $this->processed[] = [
            (string) $file,
            array_map(
                static function (mixed $file): string {
                    return (string) $file;
                },
                $resolved,
            ),
        ];
    }
}
