<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Generator;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;

/**
 * @internal
 */
#[CoversClass(Processor::class)]
#[CoversClass(Executor::class)]
final class ProcessorTest extends TestCase {
    public function testRun(): void {
        $mock = Mockery::mock(Task::class);
        $mock
            ->shouldReceive('getExtensions')
            ->once()
            ->andReturns(['php']);

        $taskA = new class() implements Task {
            /**
             * @var array<array-key, string>
             */
            public array $processed = [];

            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['htm'];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function __invoke(Directory $root, File $file): bool {
                $this->processed[] = (string) $root->getRelativePath($file);

                return true;
            }
        };
        $taskB = new class() implements Task {
            /**
             * @var array<array-key, array{string, array<string, mixed>}>
             */
            public array $processed = [];

            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['txt', 'md'];
            }

            /**
             * @return Generator<mixed, Dependency<*>, mixed, bool>
             */
            #[Override]
            public function __invoke(Directory $root, File $file): Generator {
                $resolved     = [];
                $dependencies = match ($file->getName()) {
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
                    default  => [
                        // empty
                    ],
                };

                foreach ($dependencies as $dependency) {
                    $resolved[$dependency] = yield new FileReference($dependency);
                }

                $this->processed[] = [
                    (string) $root->getRelativePath($file),
                    array_map(
                        static function (mixed $file) use ($root): mixed {
                            return (string) match (true) {
                                $file instanceof File => $root->getRelativePath($file),
                                default               => null,
                            };
                        },
                        $resolved,
                    ),
                ];

                return true;
            }
        };

        $root   = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $count  = 0;
        $events = [];

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($mock)
            ->task($taskA)
            ->task($taskB)
            ->run(
                $root,
                ['excluded.txt', '**/**/excluded.txt'],
                static function (FilePath $path, Result $result) use (&$count, &$events): void {
                    $events[(string) $path] = $result;
                    $count++;
                },
            );

        self::assertEquals(
            [
                'b/a/ba.txt'         => Result::Success,
                'c.txt'              => Result::Success,
                'b/b/bb.txt'         => Result::Success,
                'a/a.txt'            => Result::Success,
                'a/a/aa.txt'         => Result::Success,
                'a/b/ab.txt'         => Result::Success,
                'b/b.txt'            => Result::Success,
                'c.htm'              => Result::Success,
                'c.html'             => Result::Skipped,
                'a/excluded.txt'     => Result::Skipped,
                '../../../README.md' => Result::Skipped,
            ],
            $events,
        );
        self::assertCount($count, $events);
        self::assertEquals(
            [
                'c.htm',
            ],
            $taskA->processed,
        );
        self::assertEquals(
            [
                [
                    'b/a/ba.txt',
                    [],
                ],
                [
                    'c.txt',
                    [],
                ],
                [
                    'b/b/bb.txt',
                    [
                        '../../b/a/ba.txt'         => 'b/a/ba.txt',
                        '../../c.txt'              => 'c.txt',
                        '../../../../../README.md' => '../../../README.md',
                    ],
                ],
                [
                    'a/a.txt',
                    [
                        '../b/b/bb.txt' => 'b/b/bb.txt',
                        '../c.txt'      => 'c.txt',
                        '../c.html'     => 'c.html',
                        'excluded.txt'  => 'a/excluded.txt',
                    ],
                ],
                [
                    'a/a/aa.txt',
                    [],
                ],
                [
                    'a/b/ab.txt',
                    [],
                ],
                [
                    'b/b.txt',
                    [],
                ],
            ],
            $taskB->processed,
        );
    }

    public function testRunPostpone(): void {
        $task = new class() implements Task {
            /**
             * @var array<array-key, string>
             */
            public array $processed = [];
            /**
             * @var array<string, bool>
             */
            public array $postponed = [];

            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['txt', 'htm', 'html'];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function __invoke(Directory $root, File $file): ?bool {
                // Postponed?
                $path = (string) $root->getRelativePath($file);

                if ($file->getExtension() === 'html' && !isset($this->postponed[$path])) {
                    $this->postponed[$path] = true;

                    return null;
                }

                // Process
                $this->processed[] = $path;

                return true;
            }
        };

        $root   = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $count  = 0;
        $events = [];

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($task)
            ->run(
                $root,
                ['excluded.txt', '**/**/excluded.txt'],
                static function (FilePath $path, Result $result) use (&$count, &$events): void {
                    $events[(string) $path] = $result;
                    $count++;
                },
            );

        self::assertEquals(
            [
                'b/a/ba.txt' => Result::Success,
                'c.txt'      => Result::Success,
                'b/b/bb.txt' => Result::Success,
                'a/a.txt'    => Result::Success,
                'a/a/aa.txt' => Result::Success,
                'a/b/ab.txt' => Result::Success,
                'b/b.txt'    => Result::Success,
                'c.htm'      => Result::Success,
                'c.html'     => Result::Success,
                'b/b.html'   => Result::Success,
                'a/a.html'   => Result::Success,
            ],
            $events,
        );
        self::assertCount($count, $events);
        self::assertEquals(
            [
                'a/a.txt',
                'a/a/aa.txt',
                'a/b/ab.txt',
                'b/a/ba.txt',
                'b/b.txt',
                'b/b/bb.txt',
                'c.htm',
                'c.txt',
                'c.html',
                'b/b.html',
                'a/a.html',
            ],
            $task->processed,
        );
        self::assertEquals(
            [
                'c.html'   => true,
                'b/b.html' => true,
                'a/a.html' => true,
            ],
            $task->postponed,
        );
    }

    public function testRunWildcard(): void {
        $taskA = new class() implements Task {
            /**
             * @var array<array-key, string>
             */
            public array $processed = [];

            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['html'];
            }

            /**
             * @return Generator<mixed, Dependency<*>, mixed, bool>
             */
            #[Override]
            public function __invoke(Directory $root, File $file): Generator {
                $dependencies = match ($file->getName()) {
                    'b.html' => [
                        '../../../../README.md',
                        '../a/excluded.txt',
                    ],
                    default  => [
                        // empty
                    ],
                };

                foreach ($dependencies as $dependency) {
                    yield new FileReference($dependency);
                }

                $this->processed[] = (string) $root->getRelativePath($file);

                return true;
            }
        };
        $taskB = new class() implements Task {
            /**
             * @var array<array-key, string>
             */
            public array $processed = [];

            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['*'];
            }

            #[Override]
            public function __invoke(Directory $root, File $file): bool {
                $this->processed[] = (string) $root->getRelativePath($file);

                return true;
            }
        };

        $root   = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $count  = 0;
        $events = [];

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($taskA)
            ->task($taskB)
            ->run(
                $root,
                ['excluded.txt', '**/**/excluded.txt'],
                static function (FilePath $path, Result $result) use (&$count, &$events): void {
                    $events[(string) $path] = $result;
                    $count++;
                },
            );

        self::assertEquals(
            [
                'b/a/ba.txt'         => Result::Success,
                'c.txt'              => Result::Success,
                'b/b/bb.txt'         => Result::Success,
                'a/a.txt'            => Result::Success,
                'a/a/aa.txt'         => Result::Success,
                'a/b/ab.txt'         => Result::Success,
                'b/b.txt'            => Result::Success,
                'c.htm'              => Result::Success,
                'c.html'             => Result::Success,
                'a/excluded.txt'     => Result::Skipped,
                '../../../README.md' => Result::Skipped,
                'a/a.html'           => Result::Success,
                'b/b.html'           => Result::Success,
            ],
            $events,
        );
        self::assertCount($count, $events);
        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            $taskA->processed,
        );
        self::assertEquals(
            [
                'a/a.html',
                'a/a.txt',
                'a/a/aa.txt',
                'a/b/ab.txt',
                'b/a/ba.txt',
                'b/b.html',
                'b/b.txt',
                'b/b/bb.txt',
                'c.htm',
                'c.html',
                'c.txt',
            ],
            $taskB->processed,
        );
    }

    public function testRunFileNotFound(): void {
        $task = new class() implements Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['txt'];
            }

            /**
             * @return Generator<mixed, Dependency<*>, mixed, bool>
             */
            #[Override]
            public function __invoke(Directory $root, File $file): Generator {
                yield new FileReference('404.html');

                return true;
            }
        };

        $root = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();

        self::expectException(DependencyNotFound::class);
        self::expectExceptionMessage("Dependency `404.html` of `a/a.txt` not found (root: `{$root}`).");

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($task)
            ->run($root);
    }

    public function testRunCircularDependency(): void {
        $task = new class() implements Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['txt'];
            }

            /**
             * @return Generator<mixed, Dependency<*>, mixed, bool>
             */
            #[Override]
            public function __invoke(Directory $root, File $file): Generator {
                match ($file->getName()) {
                    'a.txt'  => yield new FileReference('../b/b.txt'),
                    'b.txt'  => yield new FileReference('../b/a/ba.txt'),
                    'ba.txt' => yield new FileReference('../../c.txt'),
                    'c.txt'  => yield new FileReference('a/a.txt'),
                    default  => null,
                };

                return true;
            }
        };

        $root = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();

        self::expectException(CircularDependency::class);
        self::expectExceptionMessage(
            <<<MESSAGE
            Circular Dependency detected:

            * a/a.txt
            * b/b.txt
            * b/a/ba.txt
            * c.txt
            ! a/a.txt

            (root: `{$root}`)
            MESSAGE,
        );

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($task)
            ->run($root);
    }

    public function testRunCircularDependencySelf(): void {
        $task = new class() implements Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['txt'];
            }

            /**
             * @return Generator<mixed, Dependency<*>, mixed, bool>
             */
            #[Override]
            public function __invoke(Directory $root, File $file): Generator {
                match ($file->getName()) {
                    'c.txt' => yield new FileReference('c.txt'),
                    default => null,
                };

                return true;
            }
        };

        $root = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();

        self::expectException(CircularDependency::class);
        self::expectExceptionMessage(
            <<<MESSAGE
            Circular Dependency detected:

            * c.txt
            ! c.txt

            (root: `{$root}`)
            MESSAGE,
        );

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($task)
            ->run($root);
    }
}
