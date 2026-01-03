<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithPathComparator;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as ResolverContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\HookResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\ProcessResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\TaskResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyCircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Executor;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Iterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Executor\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters\SymfonyFileSystem;
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
        $input  = (new DirectoryPath(self::getTestData()->path('')))->normalized();
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
            ],
        ]);
        $taskC = new class() extends ProcessorTest__Task {
            #[Override]
            public static function glob(): string {
                return '*.htm';
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file): void {
                parent::__invoke($resolver, $file);

                $resolver->queue(
                    new FilePath('../'.basename(__FILE__)),
                );
            }
        };
        $taskD = new class() implements HookTask {
            #[Override]
            public static function hook(): Hook {
                return Hook::File;
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
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

        $result = $processor(
            $input,
            $input->parent(),
            ['excluded.txt', '**/**/excluded.txt'],
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        self::assertTrue($result);
        self::assertEquals(
            [
                new ProcessBegin(
                    $input,
                    $input->parent(),
                    [
                        '**/*.php',
                        '**/*.htm',
                        '**/*.txt',
                        '**/*.md',
                    ],
                    [
                        'excluded.txt',
                        '**/**/excluded.txt',
                    ],
                ),
                new FileBegin($input->file('a/a.txt')),
                new TaskBegin($taskB::class),
                new DependencyBegin($input->file('b/b/bb.txt')),
                new FileBegin($input->file('b/b/bb.txt')),
                new TaskBegin($taskB::class),
                new DependencyBegin($input->file('b/a/ba.txt')),
                new FileBegin($input->file('b/a/ba.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new DependencyEnd(DependencyResult::Resolved),
                new DependencyBegin($input->file('c.txt')),
                new FileBegin($input->file('c.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new DependencyEnd(DependencyResult::Resolved),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new DependencyEnd(DependencyResult::Resolved),
                new DependencyBegin($input->file('c.txt')),
                new DependencyEnd(DependencyResult::Resolved),
                new DependencyBegin($input->file('c.html')),
                new FileBegin($input->file('c.html')),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new DependencyEnd(DependencyResult::Resolved),
                new DependencyBegin($input->file('a/excluded.txt')),
                new DependencyEnd(DependencyResult::Resolved),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/a/aa.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/b/ab.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('c.htm')),
                new TaskBegin($taskA::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskC::class),
                new DependencyBegin($input->file('../ProcessorTest.php')),
                new DependencyEnd(DependencyResult::Queued),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskD::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('c.htm'),
                    [],
                ],
            ],
            $taskA->processed,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('b/a/ba.txt'),
                    [],
                ],
                [
                    (string) $input->file('c.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/b/bb.txt'),
                    [
                        '../../b/a/ba.txt' => (string) $input->file('b/a/ba.txt'),
                        '../../c.txt'      => (string) $input->file('c.txt'),
                    ],
                ],
                [
                    (string) $input->file('a/a.txt'),
                    [
                        '../b/b/bb.txt' => (string) $input->file('b/b/bb.txt'),
                        '../c.txt'      => (string) $input->file('c.txt'),
                        '../c.html'     => (string) $input->file('c.html'),
                        'excluded.txt'  => (string) $input->file('a/excluded.txt'),
                    ],
                ],
                [
                    (string) $input->file('a/a/aa.txt'),
                    [],
                ],
                [
                    (string) $input->file('a/b/ab.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/b.txt'),
                    [],
                ],
            ],
            $taskB->processed,
        );
    }

    public function testRunFile(): void {
        $task      = new ProcessorTest__Task();
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->normalized();
        $events    = [];
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);

        $processor($input, onEvent: static function (Event $event) use (&$events): void {
            $events[] = $event;
        });

        self::assertEquals(
            [
                new ProcessBegin($input->directory(), $input->directory(), ['**/*.txt', '**/*.md'], []),
                new FileBegin($input->file('excluded.txt')),
                new TaskBegin(ProcessorTest__Task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('excluded.txt'),
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
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->normalized();
        $events    = [];
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($taskA);
        $processor->task($taskB);
        $processor->task($taskC, -1);

        $processor($input, onEvent: static function (Event $event) use (&$events): void {
            $events[] = $event;
        });

        self::assertEquals(
            [
                new ProcessBegin($input->directory(), $input->directory(), ['**/*.txt', '**/*.md', '**/*'], []),
                new FileBegin($input->file('excluded.txt')),
                new TaskBegin($taskC::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskA::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('excluded.txt'),
                    [
                        // empty
                    ],
                ],
            ],
            $taskA->processed,
        );
    }

    public function testRunWildcard(): void {
        $input     = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $events    = [];
        $taskA     = new class([
            'b.html' => [
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

        $processor(
            $input,
            null,
            ['excluded.txt', '**/**/excluded.txt'],
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        self::assertEquals(
            [
                new ProcessBegin(
                    $input,
                    $input,
                    [
                        '**/*.html',
                        '**/*',
                    ],
                    [
                        'excluded.txt',
                        '**/**/excluded.txt',
                    ],
                ),
                new FileBegin($input->file('a/a.html')),
                new TaskBegin($taskA::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/a.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/a/aa.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/b/ab.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/a/ba.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b.html')),
                new TaskBegin($taskA::class),
                new DependencyBegin($input->file('a/excluded.txt')),
                new DependencyEnd(DependencyResult::Resolved),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b/bb.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('c.htm')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('c.html')),
                new TaskBegin($taskA::class),
                new TaskEnd(TaskResult::Success),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('c.txt')),
                new TaskBegin($taskB::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('a/a.html'),
                    [],
                ],
                [
                    (string) $input->file('b/b.html'),
                    [
                        '../a/excluded.txt' => (string) $input->file('a/excluded.txt'),
                    ],
                ],
                [
                    (string) $input->file('c.html'),
                    [],
                ],
            ],
            $taskA->processed,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('a/a.html'),
                    [],
                ],
                [
                    (string) $input->file('a/a.txt'),
                    [],
                ],
                [
                    (string) $input->file('a/a/aa.txt'),
                    [],
                ],
                [
                    (string) $input->file('a/b/ab.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/a/ba.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/b.html'),
                    [],
                ],
                [
                    (string) $input->file('b/b.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/b/bb.txt'),
                    [],
                ],
                [
                    (string) $input->file('c.htm'),
                    [],
                ],
                [
                    (string) $input->file('c.html'),
                    [],
                ],
                [
                    (string) $input->file('c.txt'),
                    [],
                ],
            ],
            $taskB->processed,
        );
    }

    public function testRunOutputInsideInput(): void {
        $input     = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $output    = $input->directory('a');
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

        $processor(
            $input,
            $output,
            ['excluded.txt', '**/**/excluded.txt'],
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        self::assertEquals(
            [
                new ProcessBegin(
                    $input,
                    $output,
                    [
                        '**/*.txt',
                        '**/*.md',
                    ],
                    [
                        'excluded.txt',
                        '**/**/excluded.txt',
                        'a/**',
                    ],
                ),
                new FileBegin($input->file('b/a/ba.txt')),
                new TaskBegin($task::class),
                new DependencyBegin($output->file('a.txt')),
                new DependencyEnd(DependencyResult::Resolved),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/b/bb.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('c.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('b/a/ba.txt'),
                    [
                        '../../a/a.txt' => (string) $input->file('a/a.txt'),
                    ],
                ],
                [
                    (string) $input->file('b/b.txt'),
                    [],
                ],
                [
                    (string) $input->file('b/b/bb.txt'),
                    [],
                ],
                [
                    (string) $input->file('c.txt'),
                    [],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunFileNotFound(): void {
        $input     = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $task      = new ProcessorTest__Task(['*' => ['404.html']]);
        $path      = $input->file('a/404.html');
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);

        self::expectExceptionObject(new PathNotFound($path));

        $processor($input);
    }

    public function testRunCircularDependency(): void {
        $input     = (new DirectoryPath(self::getTestData()->path('')))->normalized();
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

            * {$input->file('a/a.txt')}
            * {$input->file('b/b.txt')}
            * {$input->file('b/a/ba.txt')}
            * {$input->file('c.txt')}
            ! {$input->file('a/a.txt')}
            MESSAGE,
        );

        $processor($input);
    }

    public function testRunCircularDependencySelf(): void {
        $input     = (new DirectoryPath(self::getTestData()->path('a/a')))->normalized();
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
                    (string) $input->file('aa.txt'),
                    [
                        'aa.txt' => (string) $input->file('aa.txt'),
                    ],
                ],
                [
                    (string) $input->file('excluded.txt'),
                    [
                        // empty
                    ],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunCircularDependencySelfThrough(): void {
        $input     = (new DirectoryPath(self::getTestData()->path('a/a')))->normalized();
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

            * {$input->file('aa.txt')}
            * {$input->file('excluded.txt')}
            ! {$input->file('aa.txt')}
            MESSAGE,
        );

        $processor($input);
    }

    public function testRunCircularDependencyNotWritable(): void {
        $events    = [];
        $output    = (new DirectoryPath(self::getTestData()->path('b')))->normalized();
        $input     = (new DirectoryPath(self::getTestData()->path('a')))->normalized();
        $task      = new ProcessorTest__Task([
            'aa.txt' => ['../a.txt'],
        ]);
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);

        $processor(
            $input,
            $output,
            ['excluded.txt', '**/**/excluded.txt'],
            static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
        );

        self::assertEquals(
            [
                new ProcessBegin(
                    $input,
                    $output,
                    [
                        '**/*.txt',
                        '**/*.md',
                    ],
                    [
                        'excluded.txt',
                        '**/**/excluded.txt',
                    ],
                ),
                new FileBegin($input->file('a.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('a/aa.txt')),
                new TaskBegin($task::class),
                new DependencyBegin($input->file('a.txt')),
                new DependencyEnd(DependencyResult::Resolved),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new FileBegin($input->file('b/ab.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Success),
                new FileEnd(FileResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
        self::assertEquals(
            [
                [
                    (string) $input->file('a.txt'),
                    [],
                ],
                [
                    (string) $input->file('a/aa.txt'),
                    [
                        '../a.txt' => (string) $input->file('a.txt'),
                    ],
                ],
                [
                    (string) $input->file('b/ab.txt'),
                    [],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunHookBeforeProcessing(): void {
        $events    = [];
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->normalized();
        $task      = new class() implements HookTask {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function hook(): array {
                return [Hook::Before];
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
                $resolver->get(new FilePath('c.txt'));
                $resolver->queue(new FilePath('c.htm'));
            }
        };
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);

        $processor($input, onEvent: static function (Event $event) use (&$events): void {
            $events[] = $event;
        });

        self::assertEquals(
            [
                new ProcessBegin($input->directory(), $input->directory(), [], []),
                new HookBegin(Hook::Before, $input->file('excluded.txt')),
                new TaskBegin($task::class),
                new DependencyBegin($input->file('c.txt')),
                new DependencyEnd(DependencyResult::Resolved),
                new DependencyBegin($input->file('c.htm')),
                new DependencyEnd(DependencyResult::Queued),
                new TaskEnd(TaskResult::Success),
                new HookEnd(HookResult::Success),
                new FileBegin($input->file('excluded.txt')),
                new FileEnd(FileResult::Skipped),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
    }

    public function testRunHookAfterProcessing(): void {
        $events    = [];
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->normalized();
        $task      = new class() implements HookTask {
            #[Override]
            public static function hook(): Hook {
                return Hook::After;
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
                $resolver->get(new FilePath('c.txt'));
            }
        };
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);

        $processor($input, onEvent: static function (Event $event) use (&$events): void {
            $events[] = $event;
        });

        self::assertEquals(
            [
                new ProcessBegin($input->directory(), $input->directory(), [], []),
                new FileBegin($input->file('excluded.txt')),
                new FileEnd(FileResult::Skipped),
                new HookBegin(Hook::After, $input->file('excluded.txt')),
                new TaskBegin($task::class),
                new DependencyBegin($input->file('c.txt')),
                new DependencyEnd(DependencyResult::Resolved),
                new TaskEnd(TaskResult::Success),
                new HookEnd(HookResult::Success),
                new ProcessEnd(ProcessResult::Success),
            ],
            $events,
        );
    }

    public function testRunHookAfterProcessingQueue(): void {
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->normalized();
        $task      = new class() implements HookTask {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function hook(): array {
                return [Hook::After];
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
                $resolver->queue(new FilePath('c.txt'));
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

    public function testRunOnError(): void {
        $exception = null;
        $events    = [];
        $input     = (new FilePath(self::getTestData()->path('excluded.txt')))->normalized();
        $task      = new class() implements HookTask {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function hook(): array {
                return [Hook::Before];
            }

            #[Override]
            public function __invoke(ResolverContract $resolver, File $file, Hook $hook): void {
                throw new Exception();
            }
        };
        $processor = new Processor(
            $this->app()->make(ContainerResolver::class),
            new ProcessorTest__Adapter(),
        );

        $processor->task($task);

        $result = $processor(
            $input,
            onEvent: static function (Event $event) use (&$events): void {
                $events[] = $event;
            },
            onError: static function (Exception $e) use (&$exception): void {
                $exception = $e;
            },
        );

        self::assertFalse($result);
        self::assertInstanceOf(Exception::class, $exception);
        self::assertEquals(
            [
                new ProcessBegin($input->directory(), $input->directory(), [], []),
                new HookBegin(Hook::Before, $input->file('excluded.txt')),
                new TaskBegin($task::class),
                new TaskEnd(TaskResult::Error),
                new HookEnd(HookResult::Error),
                new ProcessEnd(ProcessResult::Error),
            ],
            $events,
        );
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
         * @var array<string, list<non-empty-string>>
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
    public function __invoke(ResolverContract $resolver, File $file): void {
        $resolved     = [];
        $dependencies = $this->dependencies[$file->name] ?? $this->dependencies['*'] ?? [];

        foreach ($dependencies as $dependency) {
            $resolved[$dependency] = $resolver->get(new FilePath($dependency));
        }

        $this->processed[] = [
            (string) $file->path,
            array_map(
                static function (File $file): string {
                    return (string) $file->path;
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
        ?Closure $include = null,
        ?Closure $exclude = null,
    ): Finder {
        return parent::getFinder($directory, $include, $exclude)
            ->sortByName(true);
    }
}
