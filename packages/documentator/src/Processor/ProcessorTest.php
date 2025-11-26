<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithPathComparator;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessingStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinished;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskFinishedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskStarted;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyCircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Executor;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Iterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters\SymfonyFileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Hook;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Finder\Finder;

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
    use WithPathComparator;

    public function testRun(): void {
        $input  = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $events = [];

        $mock = Mockery::mock(FileTask::class);
        $mock
            ->shouldReceive('glob')
            ->once()
            ->andReturns(['*.php']);

        $taskA = new class() extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*.htm';
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
            #[Override]
            public static function glob(): string {
                return '*.htm';
            }

            #[Override]
            public function __invoke(DependencyResolver $resolver, File $file): void {
                parent::__invoke($resolver, $file);

                $resolver->queue(
                    new FileReference('../'.basename(__FILE__)),
                );
            }
        };
        $taskD = new class() implements HookTask {
            #[Override]
            public static function hook(): Hook {
                return Hook::File;
            }

            #[Override]
            public function __invoke(DependencyResolver $resolver, File $file, Hook $hook): void {
                // empty
            }
        };

        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );
        $processor->task($mock);
        $processor->task($taskA);
        $processor->task($taskB);
        $processor->task($taskC);
        $processor->task($taskD);
        $processor->listen(
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        $processor($input, null, ['excluded.txt', '**/**/excluded.txt']);

        self::assertEquals(
            [
                new ProcessingStarted($input, $input),
                new FileStarted($input->getFilePath('a/a.txt')),
                new TaskStarted($taskB::class),
                new DependencyResolved($input->getFilePath('b/b/bb.txt'), DependencyResolvedResult::Success),
                new FileStarted($input->getFilePath('b/b/bb.txt')),
                new TaskStarted($taskB::class),
                new DependencyResolved($input->getFilePath('b/a/ba.txt'), DependencyResolvedResult::Success),
                new FileStarted($input->getFilePath('b/a/ba.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskD::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new DependencyResolved($input->getFilePath('c.txt'), DependencyResolvedResult::Success),
                new FileStarted($input->getFilePath('c.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskD::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new DependencyResolved(
                    $input->getFilePath('../../../README.md'),
                    DependencyResolvedResult::Success,
                ),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskD::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new DependencyResolved($input->getFilePath('c.txt'), DependencyResolvedResult::Success),
                new DependencyResolved($input->getFilePath('c.html'), DependencyResolvedResult::Success),
                new FileStarted($input->getFilePath('c.html')),
                new TaskStarted($taskD::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new DependencyResolved($input->getFilePath('a/excluded.txt'), DependencyResolvedResult::Success),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskD::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('a/a/aa.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskD::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('a/b/ab.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskD::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('b/b.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskD::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('c.htm')),
                new TaskStarted($taskA::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskC::class),
                new DependencyResolved(
                    $input->getFilePath('../ProcessorTest.php'),
                    DependencyResolvedResult::Queued,
                ),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskD::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
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
        $task      = new ProcessorTest__Task();
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->getNormalizedPath();
        $events    = [];
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);
        $processor->listen(
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        $processor($input);

        self::assertEquals(
            [
                new ProcessingStarted($input->getDirectoryPath(), $input->getDirectoryPath()),
                new FileStarted($input->getFilePath('excluded.txt')),
                new TaskStarted(ProcessorTest__Task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
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
        $taskA     = new ProcessorTest__Task();
        $taskB     = new class() extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*';
            }
        };
        $taskC     = new class() extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*';
            }
        };
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->getNormalizedPath();
        $events    = [];
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($taskA);
        $processor->task($taskB);
        $processor->task($taskC, -1);
        $processor->listen(
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        $processor($input);

        self::assertEquals(
            [
                new ProcessingStarted($input->getDirectoryPath(), $input->getDirectoryPath()),
                new FileStarted($input->getFilePath('excluded.txt')),
                new TaskStarted($taskC::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskA::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
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
        $input     = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $events    = [];
        $taskA     = new class([
            'b.html' => [
                '../../../../README.md',
                '../a/excluded.txt',
            ],
        ]) extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*.html';
            }
        };
        $taskB     = new class() extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*';
            }
        };
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($taskA);
        $processor->task($taskB);
        $processor->listen(
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        $processor($input, null, ['excluded.txt', '**/**/excluded.txt']);

        self::assertEquals(
            [
                new ProcessingStarted($input, $input),
                new FileStarted($input->getFilePath('a/a.html')),
                new TaskStarted($taskA::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('a/a.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('a/a/aa.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('a/b/ab.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('b/a/ba.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('b/b.html')),
                new TaskStarted($taskA::class),
                new DependencyResolved(
                    $input->getFilePath('../../../README.md'),
                    DependencyResolvedResult::Success,
                ),
                new DependencyResolved($input->getFilePath('a/excluded.txt'), DependencyResolvedResult::Success),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('b/b.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('b/b/bb.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('c.htm')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('c.html')),
                new TaskStarted($taskA::class),
                new TaskFinished(TaskFinishedResult::Success),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('c.txt')),
                new TaskStarted($taskB::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
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
        $input     = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $output    = $input->getDirectoryPath('a');
        $events    = [];
        $task      = new ProcessorTest__Task([
            'ba.txt' => [
                '../../a/a.txt',
            ],
        ]);
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);
        $processor->listen(
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        $processor($input, $output, ['excluded.txt', '**/**/excluded.txt']);

        self::assertEquals(
            [
                new ProcessingStarted($input, $output),
                new FileStarted($input->getFilePath('b/a/ba.txt')),
                new TaskStarted($task::class),
                new DependencyResolved($output->getFilePath('a.txt'), DependencyResolvedResult::Success),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('b/b.txt')),
                new TaskStarted($task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('b/b/bb.txt')),
                new TaskStarted($task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('c.txt')),
                new TaskStarted($task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
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
        $input     = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task      = new ProcessorTest__Task(['*' => ['404.html']]);
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);

        self::expectException(DependencyUnresolvable::class);
        self::expectExceptionMessage('Dependency not found.');

        $processor($input);
    }

    public function testRunCircularDependency(): void {
        $input     = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task      = new ProcessorTest__Task([
            'a.txt'  => ['../b/b.txt'],
            'b.txt'  => ['../b/a/ba.txt'],
            'ba.txt' => ['../../c.txt'],
            'c.txt'  => ['a/a.txt'],
        ]);
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );
        $processor->task($task);

        self::expectException(DependencyCircularDependency::class);
        self::expectExceptionMessage(
            <<<MESSAGE
            Circular Dependency detected:

            * {$input->getFilePath('a/a.txt')}
            * {$input->getFilePath('b/b.txt')}
            * {$input->getFilePath('b/a/ba.txt')}
            * {$input->getFilePath('c.txt')}
            ! {$input->getFilePath('a/a.txt')}
            MESSAGE,
        );

        $processor($input);
    }

    public function testRunCircularDependencySelf(): void {
        $input     = (new DirectoryPath(self::getTestData()->path('a/a')))->getNormalizedPath();
        $task      = new ProcessorTest__Task([
            'aa.txt' => ['aa.txt'],
        ]);
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);

        $processor($input);

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
        $input     = (new DirectoryPath(self::getTestData()->path('a/a')))->getNormalizedPath();
        $task      = new ProcessorTest__Task([
            'aa.txt'       => ['excluded.txt'],
            'excluded.txt' => ['aa.txt'],
        ]);
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);

        self::expectException(DependencyCircularDependency::class);
        self::expectExceptionMessage(
            <<<MESSAGE
            Circular Dependency detected:

            * {$input->getFilePath('aa.txt')}
            * {$input->getFilePath('excluded.txt')}
            ! {$input->getFilePath('aa.txt')}
            MESSAGE,
        );

        $processor($input);
    }

    public function testRunCircularDependencyNotWritable(): void {
        $events    = [];
        $output    = (new DirectoryPath(self::getTestData()->path('b')))->getNormalizedPath();
        $input     = (new DirectoryPath(self::getTestData()->path('a')))->getNormalizedPath();
        $task      = new ProcessorTest__Task([
            'aa.txt' => ['../a.txt'],
        ]);
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);
        $processor->listen(
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        $processor($input, $output, ['excluded.txt', '**/**/excluded.txt']);

        self::assertEquals(
            [
                new ProcessingStarted($input, $output),
                new FileStarted($input->getFilePath('a.txt')),
                new TaskStarted($task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('a/aa.txt')),
                new TaskStarted($task::class),
                new DependencyResolved($input->getFilePath('a.txt'), DependencyResolvedResult::Success),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
                new FileStarted($input->getFilePath('b/ab.txt')),
                new TaskStarted($task::class),
                new TaskFinished(TaskFinishedResult::Success),
                new FileFinished(FileFinishedResult::Success),
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

    public function testRunHookBeforeProcessing(): void {
        $events    = [];
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->getNormalizedPath();
        $task      = new class() implements HookTask {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function hook(): array {
                return [Hook::BeforeProcessing];
            }

            #[Override]
            public function __invoke(DependencyResolver $resolver, File $file, Hook $hook): void {
                $resolver->resolve(new FileReference('c.txt'));
                $resolver->queue(new FileReference('c.htm'));
            }
        };
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);
        $processor->listen(
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        $processor($input);

        self::assertEquals(
            [
                new ProcessingStarted($input->getDirectoryPath(), $input->getDirectoryPath()),
                new HookStarted(Hook::BeforeProcessing, $input->getFilePath('excluded.txt')),
                new TaskStarted($task::class),
                new DependencyResolved(new FilePath('c.txt'), DependencyResolvedResult::Success),
                new DependencyResolved($input->getFilePath('c.htm'), DependencyResolvedResult::Queued),
                new TaskFinished(TaskFinishedResult::Success),
                new HookFinished(HookFinishedResult::Success),
                new FileStarted($input->getFilePath('excluded.txt')),
                new FileFinished(FileFinishedResult::Skipped),
                new ProcessingFinished(ProcessingFinishedResult::Success),
            ],
            $events,
        );
    }

    public function testRunHookAfterProcessing(): void {
        $events    = [];
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->getNormalizedPath();
        $task      = new class() implements HookTask {
            #[Override]
            public static function hook(): Hook {
                return Hook::AfterProcessing;
            }

            #[Override]
            public function __invoke(DependencyResolver $resolver, File $file, Hook $hook): void {
                $resolver->resolve(new FileReference('c.txt'));
            }
        };
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);
        $processor->listen(
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        $processor($input);

        self::assertEquals(
            [
                new ProcessingStarted($input->getDirectoryPath(), $input->getDirectoryPath()),
                new FileStarted($input->getFilePath('excluded.txt')),
                new FileFinished(FileFinishedResult::Skipped),
                new HookStarted(Hook::AfterProcessing, $input->getFilePath('excluded.txt')),
                new TaskStarted($task::class),
                new DependencyResolved(new FilePath('c.txt'), DependencyResolvedResult::Success),
                new TaskFinished(TaskFinishedResult::Success),
                new HookFinished(HookFinishedResult::Success),
                new ProcessingFinished(ProcessingFinishedResult::Success),
            ],
            $events,
        );
    }

    public function testRunHookAfterProcessingQueue(): void {
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->getNormalizedPath();
        $task      = new class() implements HookTask {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function hook(): array {
                return [Hook::AfterProcessing];
            }

            #[Override]
            public function __invoke(DependencyResolver $resolver, File $file, Hook $hook): void {
                $resolver->queue(new FileReference('c.txt'));
            }
        };
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);

        self::expectException(DependencyUnavailable::class);

        $processor($input);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProcessorTest__Task implements FileTask {
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
    public static function glob(): array|string {
        return ['*.txt', '*.md'];
    }

    #[Override]
    public function __invoke(DependencyResolver $resolver, File $file): void {
        $resolved     = [];
        $dependencies = $this->dependencies[$file->name] ?? $this->dependencies['*'] ?? [];

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

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ProcessorTest__Adapter extends SymfonyFileSystem {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getFinder(
        DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): Finder {
        return parent::getFinder($directory, $include, $exclude, $depth)
            ->sortByName(true);
    }
}
