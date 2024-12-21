<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Generator;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyCircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(Processor::class)]
#[CoversClass(Executor::class)]
final class ProcessorTest extends TestCase {
    public function testRun(): void {
        $input  = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $count  = 0;
        $events = [];

        $mock = Mockery::mock(Task::class);
        $mock
            ->shouldReceive('getExtensions')
            ->once()
            ->andReturns(['php']);

        $taskA = new class($input) extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['htm'];
            }
        };
        $taskB = new ProcessorTest__Task($input, [
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

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($mock)
            ->task($taskA)
            ->task($taskB)
            ->exclude(['excluded.txt', '**/**/excluded.txt'])
            ->run(
                $input,
                listener: static function (FilePath $path, Result $result) use (&$count, &$events): void {
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
                [
                    'c.htm',
                    [],
                ],
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

    public function testRunFile(): void {
        $path   = (new FilePath(self::getTestData()->path('excluded.txt')))->getNormalizedPath();
        $task   = new ProcessorTest__Task($path->getDirectoryPath());
        $count  = 0;
        $events = [];

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($task)
            ->run(
                $path,
                listener: static function (FilePath $path, Result $result) use (&$count, &$events): void {
                    $events[(string) $path] = $result;
                    $count++;
                },
            );

        self::assertEquals(
            [
                'excluded.txt' => Result::Success,
            ],
            $events,
        );
        self::assertCount($count, $events);
        self::assertEquals(
            [
                [
                    'excluded.txt',
                    [
                        // empty
                    ],
                ],
            ],
            $task->processed,
        );
    }

    public function testRunWildcard(): void {
        $input  = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $count  = 0;
        $events = [];
        $taskA  = new class($input, [
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
        $taskB  = new class($input) extends ProcessorTest__Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['*'];
            }
        };

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($taskA)
            ->task($taskB)
            ->exclude(['excluded.txt', '**/**/excluded.txt'])
            ->run(
                $input,
                listener: static function (FilePath $path, Result $result) use (&$count, &$events): void {
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
                [
                    'a/a.html',
                    [],
                ],
                [
                    'b/b.html',
                    [
                        '../../../../README.md' => '../../../README.md',
                        '../a/excluded.txt'     => 'a/excluded.txt',
                    ],
                ],
                [
                    'c.html',
                    [],
                ],
            ],
            $taskA->processed,
        );
        self::assertEquals(
            [
                [
                    'a/a.html',
                    [],
                ],
                [
                    'a/a.txt',
                    [],
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
                    'b/a/ba.txt',
                    [],
                ],
                [
                    'b/b.html',
                    [],
                ],
                [
                    'b/b.txt',
                    [],
                ],
                [
                    'b/b/bb.txt',
                    [],
                ],
                [
                    'c.htm',
                    [],
                ],
                [
                    'c.html',
                    [],
                ],
                [
                    'c.txt',
                    [],
                ],
            ],
            $taskB->processed,
        );
    }

    public function testRunFileNotFound(): void {
        $input = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task  = new ProcessorTest__Task($input, ['*' => ['404.html']]);

        self::expectException(DependencyUnresolvable::class);
        self::expectExceptionMessage(
            sprintf(
                'Dependency `%s` not found.',
                $input->getFilePath('a/404.html'),
            ),
        );

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($task)
            ->run($input);
    }

    public function testRunCircularDependency(): void {
        $input = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task  = new ProcessorTest__Task($input, [
            'a.txt'  => ['../b/b.txt'],
            'b.txt'  => ['../b/a/ba.txt'],
            'ba.txt' => ['../../c.txt'],
            'c.txt'  => ['a/a.txt'],
        ]);

        self::expectException(DependencyCircularDependency::class);
        self::expectExceptionMessage(
            <<<'MESSAGE'
            Circular Dependency detected:

            * <> a/a.txt
            * <> b/b.txt
            * <> b/a/ba.txt
            * <> c.txt
            ! <> a/a.txt
            MESSAGE,
        );

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($task)
            ->run($input);
    }

    public function testRunCircularDependencySelf(): void {
        $input = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task  = new ProcessorTest__Task($input, [
            'c.txt' => ['c.txt'],
        ]);

        self::expectException(DependencyCircularDependency::class);
        self::expectExceptionMessage(
            <<<'MESSAGE'
            Circular Dependency detected:

            * <> c.txt
            ! <> c.txt
            MESSAGE,
        );

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($task)
            ->run($input);
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
        private readonly DirectoryPath $root,
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

    /**
     * @return Generator<mixed, Dependency<*>, mixed, bool>
     */
    #[Override]
    public function __invoke(File $file): Generator {
        $resolved     = [];
        $dependencies = $this->dependencies[$file->getName()] ?? $this->dependencies['*'] ?? [];

        foreach ($dependencies as $dependency) {
            $resolved[$dependency] = yield new FileReference($file->getFilePath($dependency));
        }

        $this->processed[] = [
            (string) $this->root->getRelativePath($file->getPath()),
            array_map(
                function (mixed $file): mixed {
                    return (string) match (true) {
                        $file instanceof File => $this->root->getRelativePath($file->getPath()),
                        default               => null,
                    };
                },
                $resolved,
            ),
        ];

        return true;
    }
}
