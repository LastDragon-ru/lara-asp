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
                (string) $input->getFilePath('b/a/ba.txt')         => Result::Success,
                (string) $input->getFilePath('c.txt')              => Result::Success,
                (string) $input->getFilePath('b/b/bb.txt')         => Result::Success,
                (string) $input->getFilePath('a/a.txt')            => Result::Success,
                (string) $input->getFilePath('a/a/aa.txt')         => Result::Success,
                (string) $input->getFilePath('a/b/ab.txt')         => Result::Success,
                (string) $input->getFilePath('b/b.txt')            => Result::Success,
                (string) $input->getFilePath('c.htm')              => Result::Success,
                (string) $input->getFilePath('c.html')             => Result::Skipped,
                (string) $input->getFilePath('a/excluded.txt')     => Result::Skipped,
                (string) $input->getFilePath('../../../README.md') => Result::Skipped,
            ],
            $events,
        );
        self::assertCount($count, $events);
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
        $count  = 0;
        $events = [];

        (new Processor($this->app()->make(ContainerResolver::class)))
            ->task($task)
            ->run(
                $input,
                listener: static function (FilePath $path, Result $result) use (&$count, &$events): void {
                    $events[(string) $path] = $result;
                    $count++;
                },
            );

        self::assertEquals(
            [
                (string) $input->getFilePath('excluded.txt') => Result::Success,
            ],
            $events,
        );
        self::assertCount($count, $events);
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

    public function testRunWildcard(): void {
        $input  = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $count  = 0;
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
                (string) $input->getFilePath('b/a/ba.txt')         => Result::Success,
                (string) $input->getFilePath('c.txt')              => Result::Success,
                (string) $input->getFilePath('b/b/bb.txt')         => Result::Success,
                (string) $input->getFilePath('a/a.txt')            => Result::Success,
                (string) $input->getFilePath('a/a/aa.txt')         => Result::Success,
                (string) $input->getFilePath('a/b/ab.txt')         => Result::Success,
                (string) $input->getFilePath('b/b.txt')            => Result::Success,
                (string) $input->getFilePath('c.htm')              => Result::Success,
                (string) $input->getFilePath('c.html')             => Result::Success,
                (string) $input->getFilePath('a/excluded.txt')     => Result::Skipped,
                (string) $input->getFilePath('../../../README.md') => Result::Skipped,
                (string) $input->getFilePath('a/a.html')           => Result::Success,
                (string) $input->getFilePath('b/b.html')           => Result::Success,
            ],
            $events,
        );
        self::assertCount($count, $events);
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

    public function testRunFileNotFound(): void {
        $input = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task  = new ProcessorTest__Task(['*' => ['404.html']]);

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
            ->task($task)
            ->run($input);
    }

    public function testRunCircularDependencySelf(): void {
        $input = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $task  = new ProcessorTest__Task([
            'c.txt' => ['c.txt'],
        ]);

        self::expectException(DependencyCircularDependency::class);
        self::expectExceptionMessage(
            <<<MESSAGE
            Circular Dependency detected:

            * {$input}/c.txt
            ! {$input}/c.txt
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
            (string) $file,
            array_map(
                static function (mixed $file): string {
                    return (string) match (true) {
                        $file instanceof File => (string) $file,
                        default               => null,
                    };
                },
                $resolved,
            ),
        ];

        return true;
    }
}
