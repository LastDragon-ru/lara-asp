<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use ArrayIterator;
use Closure;
use Exception;
use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Traversable;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Resolver::class)]
final class ResolverTest extends TestCase {
    public function testResolve(): void {
        $run        = static function (): void {
            // empty
        };
        $queue      = static function (): void {
            // empty
        };
        $resolved   = Mockery::mock(File::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $dependency = Mockery::mock(Dependency::class);
        $dependency
            ->shouldReceive('__invoke')
            ->with($filesystem)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run, $queue]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('notify')
            ->with($dependency, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();

        self::assertSame($resolved, $resolver->resolve($dependency));
    }

    public function testResolveException(): void {
        $run        = static function (mixed $resolved): void {
            // empty
        };
        $queue      = static function (): void {
            // empty
        };
        $exception  = new Exception();
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $dependency = Mockery::mock(Dependency::class);
        $dependency
            ->shouldReceive('__invoke')
            ->with($filesystem)
            ->once()
            ->andThrow($exception);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run, $queue]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('notify')
            ->with($dependency, DependencyResolvedResult::Failed)
            ->once()
            ->andReturns();

        self::expectExceptionObject($exception);

        $resolver->resolve($dependency);
    }

    public function testResolveTraversable(): void {
        $run        = static function (): void {
            // empty
        };
        $queue      = static function (): void {
            // empty
        };
        $resolved   = Mockery::mock(Traversable::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $dependency = Mockery::mock(Dependency::class);
        $dependency
            ->shouldReceive('__invoke')
            ->with($filesystem)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run, $queue]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('notify')
            ->with($dependency, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();
        $resolver
            ->shouldReceive('iterate')
            ->with($dependency, $resolved)
            ->once()
            ->andReturn($resolved);

        self::assertSame($resolved, $resolver->resolve($dependency));
    }

    public function testIterate(): void {
        $run        = static function (): void {
            // empty
        };
        $queue      = static function (): void {
            // empty
        };
        $aFile      = Mockery::mock(File::class);
        $bFile      = Mockery::mock(File::class);
        $files      = [1 => $aFile, 3 => $bFile];
        $resolved   = new ArrayIterator($files);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $dependency = Mockery::mock(Dependency::class);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run, $queue]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('notify')
            ->with($aFile, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();
        $resolver
            ->shouldReceive('notify')
            ->with($bFile, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();

        self::assertEquals(
            $files,
            iterator_to_array($resolver->iterate($dependency, $resolved)),
        );
    }

    public function testIterateException(): void {
        $run        = static function (): void {
            // empty
        };
        $queue      = static function (): void {
            // empty
        };
        $exception  = new Exception();
        $resolved   = new class($exception) implements IteratorAggregate {
            public function __construct(
                protected Exception $exception,
            ) {
                // empty
            }

            #[Override]
            public function getIterator(): Traversable {
                throw $this->exception;
            }
        };
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $dependency = Mockery::mock(Dependency::class);
        $resolver   = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run, $queue]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('notify')
            ->with($dependency, DependencyResolvedResult::Failed)
            ->once()
            ->andReturn();

        self::expectExceptionObject($exception);

        iterator_to_array($resolver->iterate($dependency, $resolved));
    }

    public function testQueue(): void {
        $run   = static function (): void {
            // empty
        };
        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $path       = new FilePath('/file.txt');
        $resolved   = Mockery::mock(File::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run, Closure::fromCallable($queue)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('file')
            ->with($path)
            ->once()
            ->andReturn($resolved);
        $resolver
            ->shouldReceive('notify')
            ->with($resolved, DependencyResolvedResult::Queued)
            ->once()
            ->andReturns();

        $resolver->queue($path);
    }

    public function testQueueIterable(): void {
        $aPath = new FilePath('/a.txt');
        $aFile = Mockery::mock(File::class);
        $bPath = new FilePath('/b.txt');
        $bFile = Mockery::mock(File::class);
        $run   = static function (mixed $resolved): void {
            // empty
        };
        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->with($aFile)
            ->once()
            ->andReturns();
        $queue
            ->shouldReceive('__invoke')
            ->with($bFile)
            ->once()
            ->andReturns();

        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run, Closure::fromCallable($queue)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('file')
            ->with($aPath)
            ->once()
            ->andReturn($aFile);
        $resolver
            ->shouldReceive('file')
            ->with($bPath)
            ->once()
            ->andReturn($bFile);
        $resolver
            ->shouldReceive('notify')
            ->with($aFile, DependencyResolvedResult::Queued)
            ->once()
            ->andReturns();
        $resolver
            ->shouldReceive('notify')
            ->with($bFile, DependencyResolvedResult::Queued)
            ->once()
            ->andReturns();

        $resolver->queue([$aPath, $bPath]);
    }

    public function testCheck(): void {
        $exception = new Exception();
        $resolver  = new class($exception) extends Resolver {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct(Exception $exception) {
                $this->exception = $exception;
            }

            public function getException(): ?Exception {
                return $this->exception;
            }
        };

        $thrown = null;

        try {
            $resolver->check();
        } catch (Exception $e) {
            $thrown = $e;
        }

        self::assertSame($exception, $thrown);
        self::assertNull($resolver->getException());

        $resolver->check();
    }

    public function testNotify(): void {
        $path       = new FilePath('path/to/dependency');
        $filepath   = new FilePath('/path/to/file');
        $filesystem = Mockery::mock(FileSystem::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->once()
            ->with(
                Mockery::isEqual(
                    new DependencyResolved(
                        $path,
                        DependencyResolvedResult::Success,
                    ),
                ),
            )
            ->andReturn();
        $dispatcher
            ->shouldReceive('notify')
            ->once()
            ->with(
                Mockery::isEqual(
                    new DependencyResolved(
                        $filepath,
                        DependencyResolvedResult::Missed,
                    ),
                ),
            )
            ->andReturn();

        $callback = static function (File $file): void {
            // empty
        };
        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $callback, $callback]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('path')
            ->with($path)
            ->once()
            ->andReturn($path);

        $file       = Mockery::mock(File::class, [$filesystem, $filepath, Mockery::mock(Caster::class)]);
        $dependency = Mockery::mock(Dependency::class);
        $dependency
            ->shouldReceive('getPath')
            ->with($filesystem)
            ->once()
            ->andReturn($path);

        $resolver->notify($file, DependencyResolvedResult::Missed);
        $resolver->notify($dependency, DependencyResolvedResult::Success);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ResolverTest__Invokable {
    public function __invoke(File $file): void {
        // empty
    }
}
