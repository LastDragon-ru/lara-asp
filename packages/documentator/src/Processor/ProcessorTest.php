<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CircularDependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_unique;
use function count;

/**
 * @internal
 */
#[CoversClass(Processor::class)]
final class ProcessorTest extends TestCase {
    public function testRun(): void {
        $mock = Mockery::mock(Task::class);
        $task = new class() implements Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getDependencies(Directory $directory, File $file): array {
                $path = $file->getRelativePath($directory);

                return match (true) {
                    $path === 'aa.txt' => [
                        '../../b/b/bb.txt',
                        '../../c.txt',
                        '../../c.html',
                    ],
                    $path === 'bb.txt' => [
                        '../../b/a/ba.txt',
                        '../../c.txt',
                    ],
                    default            => [
                        // empty
                    ],
                };
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function run(Directory $directory, File $file, array $dependencies): bool {
                return true;
            }
        };

        $root   = Path::normalize(self::getTestData()->path(''));
        $events = [];

        (new Processor())
            ->task('php', $mock)
            ->task('txt', $task)
            ->run($root, static function (string $path, bool $result, float $duration) use (&$events): void {
                $events[] = $path;
            });

        self::assertEquals(
            [
                'b/a/ba.txt',
                'c.txt',
                'b/b/bb.txt',
                'a/a/aa.txt',
                'a/a.txt',
                'a/b/ab.txt',
                'b/b.txt',
            ],
            $events,
        );
        self::assertCount(
            count(array_unique($events)),
            $events,
        );
    }

    public function testRunCircularDependency(): void {
        $task = new class() implements Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getDependencies(Directory $directory, File $file): array {
                $path = $file->getRelativePath($directory);

                return match (true) {
                    $path === 'a.txt'  => [
                        '../b/b.txt',
                    ],
                    $path === 'b.txt'  => [
                        '../b/a/ba.txt',
                    ],
                    $path === 'ba.txt' => [
                        '../../c.txt',
                    ],
                    $path === 'c.txt'  => [
                        'a/a.txt',
                    ],
                    default            => [
                        // empty
                    ],
                };
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function run(Directory $directory, File $file, array $dependencies): bool {
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
            ->task('txt', $task)
            ->run($root);
    }

    public function testRunCircularDependencySelf(): void {
        $task = new class() implements Task {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getDependencies(Directory $directory, File $file): array {
                $path = $file->getRelativePath($directory);

                return match (true) {
                    $path === 'c.txt' => [
                        'c.txt',
                    ],
                    default           => [
                        // empty
                    ],
                };
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function run(Directory $directory, File $file, array $dependencies): bool {
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
            ->task('txt', $task)
            ->run($root);
    }
}
