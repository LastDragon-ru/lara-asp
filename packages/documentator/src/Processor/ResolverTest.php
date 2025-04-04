<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Mockery\PropertiesMock;
use LastDragon_ru\LaraASP\Testing\Mockery\WithProperties;
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
    public function testInvoke(): void {
        $run        = static function (File $file, mixed $resolved): mixed {
            return $resolved;
        };
        $file       = Mockery::mock(File::class);
        $resolved   = Mockery::mock(File::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $dependency = Mockery::mock(Dependency::class);
        $dependency
            ->shouldReceive('__invoke')
            ->with($filesystem)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $file, $run]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('notify')
            ->with($dependency, $resolved)
            ->once()
            ->andReturn($resolved);

        self::assertSame($resolved, $resolver($dependency));
    }

    public function testInvokeException(): void {
        $run        = static function (File $file, mixed $resolved): mixed {
            return $resolved;
        };
        $file       = Mockery::mock(File::class);
        $exception  = new Exception();
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $dependency = Mockery::mock(Dependency::class);
        $dependency
            ->shouldReceive('__invoke')
            ->with($filesystem)
            ->once()
            ->andThrow($exception);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $file, $run]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('notify')
            ->with($dependency, $exception)
            ->once()
            ->andReturn($exception);

        self::expectExceptionObject($exception);

        $resolver($dependency);
    }

    public function testInvokeTraversable(): void {
        $run        = static function (File $file, mixed $resolved): mixed {
            return $resolved;
        };
        $file       = Mockery::mock(File::class);
        $resolved   = Mockery::mock(Traversable::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $dependency = Mockery::mock(Dependency::class);
        $dependency
            ->shouldReceive('__invoke')
            ->with($filesystem)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $file, $run]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('notify')
            ->with($dependency, $resolved)
            ->once()
            ->andReturn($resolved);
        $resolver
            ->shouldReceive('iterate')
            ->with($dependency, $resolved)
            ->once()
            ->andReturn($resolved);

        self::assertSame($resolved, $resolver($dependency));
    }

    public function testIterate(): void {
        $run        = static function (File $file, mixed $resolved): mixed {
            return $resolved;
        };
        $file       = Mockery::mock(File::class);
        $aFile      = Mockery::mock(File::class);
        $bFile      = Mockery::mock(File::class);
        $files      = [1 => $aFile, 3 => $bFile];
        $resolved   = new ArrayIterator($files);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $dependency = Mockery::mock(Dependency::class);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $file, $run]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('notify')
            ->with($aFile, $aFile)
            ->once()
            ->andReturn($aFile);
        $resolver
            ->shouldReceive('notify')
            ->with($bFile, $bFile)
            ->once()
            ->andReturn($bFile);

        self::assertEquals(
            $files,
            iterator_to_array($resolver->iterate($dependency, $resolved)),
        );
    }

    public function testIterateException(): void {
        $run        = static function (File $file, mixed $resolved): mixed {
            return $resolved;
        };
        $file       = Mockery::mock(File::class);
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
        $resolver   = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $file, $run]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('notify')
            ->with($dependency, $exception)
            ->once()
            ->andReturn();

        self::expectExceptionObject($exception);

        iterator_to_array($resolver->iterate($dependency, $resolved));
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
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('getPathname')
            ->times(8)
            ->andReturnUsing(
                static function (Directory|DirectoryPath|File|FilePath $path): string {
                    return (string) $path;
                },
            );

        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->twice()
            ->with(
                Mockery::isEqual(
                    new DependencyResolved(
                        'path/to/dependency',
                        DependencyResolvedResult::Success,
                    ),
                ),
            )
            ->andReturn();
        $dispatcher
            ->shouldReceive('notify')
            ->twice()
            ->with(
                Mockery::isEqual(
                    new DependencyResolved(
                        'path/to/dependency',
                        DependencyResolvedResult::Missed,
                    ),
                ),
            )
            ->andReturn();
        $dispatcher
            ->shouldReceive('notify')
            ->twice()
            ->with(
                Mockery::isEqual(
                    new DependencyResolved(
                        'path/to/dependency',
                        DependencyResolvedResult::Failed,
                    ),
                ),
            )
            ->andReturn();
        $dispatcher
            ->shouldReceive('notify')
            ->twice()
            ->with(
                Mockery::isEqual(
                    new DependencyResolved(
                        'path/to/dependency',
                        DependencyResolvedResult::Null,
                    ),
                ),
            )
            ->andReturn();

        $resolver = Mockery::mock(Resolver::class, new WithProperties(), PropertiesMock::class);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldUseProperty('fs')
            ->value($filesystem);
        $resolver
            ->shouldUseProperty('dispatcher')
            ->value($dispatcher);

        $resolvedUnresolvable = new DependencyUnresolvable(Mockery::mock(Dependency::class), new Exception());
        $resolvedException    = new Exception();
        $resolvedObject       = Mockery::mock(File::class);
        $resolvedNull         = null;

        // Dependency
        $dependency = Mockery::mock(Dependency::class);
        $dependency
            ->shouldReceive('getPath')
            ->with($filesystem)
            ->times(4)
            ->andReturn(
                new FilePath('path/to/dependency'),
            );

        self::assertSame($resolvedUnresolvable, $resolver->notify($dependency, $resolvedUnresolvable));
        self::assertSame($resolvedException, $resolver->notify($dependency, $resolvedException));
        self::assertSame($resolvedObject, $resolver->notify($dependency, $resolvedObject));
        self::assertSame($resolvedNull, $resolver->notify($dependency, $resolvedNull)); // @phpstan-ignore staticMethod.alreadyNarrowedType (tests)

        // File
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('__toString')
            ->times(4)
            ->andReturn('path/to/dependency');

        self::assertSame($resolvedUnresolvable, $resolver->notify($file, $resolvedUnresolvable));
        self::assertSame($resolvedException, $resolver->notify($file, $resolvedException));
        self::assertSame($resolvedObject, $resolver->notify($file, $resolvedObject));
        self::assertSame($resolvedNull, $resolver->notify($file, $resolvedNull)); // @phpstan-ignore staticMethod.alreadyNarrowedType (tests)
    }
}
