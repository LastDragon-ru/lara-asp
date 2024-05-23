<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Generator;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;
use function array_unique;
use function count;

/**
 * @internal
 */
#[CoversClass(Processor::class)]
final class ProcessorTest extends TestCase {
    public function testRun(): void {
        $mock = Mockery::mock(Task::class);
        $mock
            ->shouldReceive('getExtensions')
            ->once()
            ->andReturns(['php']);

        $task = new class() implements Task {
            /**
             * @var array<array-key, array{string, array<string, ?string>}>
             */
            public array $processed = [];

            /**
             * @inheritDoc
             */
            #[Override]
            public function getExtensions(): array {
                return ['txt', 'md'];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function __invoke(Directory $root, File $file): Generator {
                $resolved     = [];
                $dependencies = match ($file->getName()) {
                    'a.txt'  => [
                        '../b/b/bb.txt',
                        '../c.txt',
                        '../c.html',
                        '404.html',
                    ],
                    'bb.txt' => [
                        '../../b/a/ba.txt',
                        '../../c.txt',
                        '../../../../../README.md',
                    ],
                    default            => [
                        // empty
                    ],
                };

                foreach ($dependencies as $dependency) {
                    $resolved[$dependency] = yield $dependency;
                }

                $this->processed[] = [
                    $file->getRelativePath($root),
                    array_map(
                        static function (?File $file) use ($root): ?string {
                            return $file?->getRelativePath($root);
                        },
                        $resolved,
                    ),
                ];

                return true;
            }
        };

        $root   = Path::normalize(self::getTestData()->path(''));
        $events = [];

        (new Processor())
            ->task($mock)
            ->task($task)
            ->run($root, static function (string $path) use (&$events): void {
                $events[] = $path;
            });

        self::assertEquals(
            [
                'b/a/ba.txt',
                'c.txt',
                'b/b/bb.txt',
                'a/a.txt',
                'a/a/aa.txt',
                'a/b/ab.txt',
                'b/b.txt',
            ],
            $events,
        );
        self::assertCount(
            count(array_unique($events)),
            $events,
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
                        '404.html'      => null,
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
            $task->processed,
        );
    }

    public function testRunCircularDependency(): void {
        $task = new class() implements Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getExtensions(): array {
                return ['txt'];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function __invoke(Directory $root, File $file): Generator {
                match ($file->getName()) {
                    'a.txt'  => yield '../b/b.txt',
                    'b.txt'  => yield '../b/a/ba.txt',
                    'ba.txt' => yield '../../c.txt',
                    'c.txt'  => yield 'a/a.txt',
                    default  => null,
                };

                return true;
            }
        };

        $root = Path::normalize(self::getTestData()->path(''));

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

        (new Processor())
            ->task($task)
            ->run($root);
    }

    public function testRunCircularDependencySelf(): void {
        $task = new class() implements Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getExtensions(): array {
                return ['txt'];
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function __invoke(Directory $root, File $file): Generator {
                match ($file->getName()) {
                    'c.txt' => yield 'c.txt',
                    default => null,
                };

                return true;
            }
        };

        $root = Path::normalize(self::getTestData()->path(''));

        self::expectException(CircularDependency::class);
        self::expectExceptionMessage(
            <<<MESSAGE
            Circular Dependency detected:

            * c.txt
            ! c.txt

            (root: `{$root}`)
            MESSAGE,
        );

        (new Processor())
            ->task($task)
            ->run($root);
    }
}
