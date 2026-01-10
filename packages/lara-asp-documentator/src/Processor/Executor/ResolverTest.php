<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResult;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use UnitEnum;

/**
 * @internal
 */
#[CoversClass(Resolver::class)]
final class ResolverTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testGet(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $save       = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $filepath   = new FilePath('file.txt');
        $resolved   = Mockery::mock(File::class);
        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);

        self::assertSame($resolved, $resolver->get($filepath));
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Found),
            ],
            $dispatcher->events,
        );
    }

    public function testGetException(): void {
        $run        = Mockery::mock(ResolverTest__Invokable::class);
        $save       = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $filepath   = new FilePath('file.txt');
        $exception  = new Exception();
        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andThrow($exception);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);

        self::expectExceptionObject($exception);

        try {
            $resolver->get($filepath);
        } finally {
            self::assertEquals(
                [
                    new Dependency($filepath, DependencyResult::Found),
                ],
                $dispatcher->events,
            );
        }
    }

    public function testFind(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $save       = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $filepath   = new FilePath('file.txt');
        $resolved   = Mockery::mock(File::class);
        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('exists')
            ->with($filepath)
            ->once()
            ->andReturn(true);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);

        self::assertSame($resolved, $resolver->find($filepath));
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Found),
            ],
            $dispatcher->events,
        );
    }

    public function testFindNotFound(): void {
        $run        = Mockery::mock(ResolverTest__Invokable::class);
        $save       = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $filepath   = new FilePath('file.txt');
        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('exists')
            ->with($filepath)
            ->once()
            ->andReturn(false);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);

        self::assertNull($resolver->find($filepath));
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::NotFound),
            ],
            $dispatcher->events,
        );
    }

    public function testFindException(): void {
        $run        = Mockery::mock(ResolverTest__Invokable::class);
        $save       = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $filepath   = new FilePath('file.txt');
        $exception  = new Exception();
        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('exists')
            ->with($filepath)
            ->once()
            ->andReturn(true);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andThrow($exception);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);

        self::expectExceptionObject($exception);

        try {
            $resolver->find($filepath);
        } finally {
            self::assertEquals(
                [
                    new Dependency($filepath, DependencyResult::Found),
                ],
                $dispatcher->events,
            );
        }
    }

    public function testSave(): void {
        $run  = Mockery::mock(ResolverTest__Invokable::class);
        $save = Mockery::mock(ResolverTest__Invokable::class);
        $save
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filepath   = new FilePath('/file.txt');
        $resolved   = Mockery::mock(FileImpl::class, [$filesystem, $filepath]);
        $content    = 'content';

        $filesystem
            ->shouldReceive('write')
            ->with($filepath, $content)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);

        $resolver->save($filepath, $content);

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
    }

    public function testSaveException(): void {
        $run        = Mockery::mock(ResolverTest__Invokable::class);
        $save       = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $content    = 'content';
        $filepath   = new FilePath('file.txt');
        $exception  = new Exception();
        $container  = Mockery::mock(Container::class);
        $dispatcher = $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('write')
            ->with($filepath, $content)
            ->once()
            ->andThrow($exception);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);

        self::expectExceptionObject($exception);

        $resolver->save($filepath, $content);

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
    }

    public function testSaveCastReset(): void {
        $dispatcher = new ResolverTest__Dispatcher();
        $container  = Mockery::mock(Container::class);
        $container
            ->shouldReceive('make')
            ->once()
            ->andReturn(
                new ResolverTest__Cast(),
            );

        $run        = (new ResolverTest__Invokable())(...);
        $save       = (new ResolverTest__Invokable())(...);
        $queue      = (new ResolverTest__Invokable())(...);
        $delete     = (new ResolverTest__Invokable())(...);
        $content    = 'content';
        $filepath   = new FilePath('/file.txt');
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = new Resolver($container, $dispatcher, $filesystem, $run, $save, $queue, $delete);
        $file       = new FileImpl($filesystem, $filepath);
        $value      = $resolver->cast($file, ResolverTest__Cast::class);

        $filesystem
            ->shouldReceive('write')
            ->with($filepath, $content)
            ->once()
            ->andReturn($file);

        $resolver->save($filepath, $content);

        self::assertNotSame($value, $resolver->cast($file, ResolverTest__Cast::class));
        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
    }

    public function testQueue(): void {
        $run   = Mockery::mock(ResolverTest__Invokable::class);
        $save  = Mockery::mock(ResolverTest__Invokable::class);
        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $filepath   = new FilePath('/file.txt');
        $resolved   = Mockery::mock(File::class);
        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);

        $resolver->queue($filepath);

        self::assertEquals(
            [
                new Dependency($filepath, DependencyResult::Queued),
            ],
            $dispatcher->events,
        );
    }

    public function testQueueIterable(): void {
        $aPath = new FilePath('/a.txt');
        $aFile = Mockery::mock(File::class);
        $bPath = new FilePath('/b.txt');
        $bFile = Mockery::mock(File::class);
        $run   = Mockery::mock(ResolverTest__Invokable::class);
        $save  = Mockery::mock(ResolverTest__Invokable::class);
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

        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($aPath)
            ->once()
            ->andReturn($aFile);
        $filesystem
            ->shouldReceive('get')
            ->with($bPath)
            ->once()
            ->andReturn($bFile);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($aPath)
            ->once()
            ->andReturn($aPath);
        $resolver
            ->shouldReceive('path')
            ->with($bPath)
            ->once()
            ->andReturn($bPath);

        $resolver->queue([$aPath, $bPath]);

        self::assertEquals(
            [
                new Dependency($aPath, DependencyResult::Queued),
                new Dependency($bPath, DependencyResult::Queued),
            ],
            $dispatcher->events,
        );
    }

    public function testDelete(): void {
        $path   = new FilePath('/file.txt');
        $run    = Mockery::mock(ResolverTest__Invokable::class);
        $save   = Mockery::mock(ResolverTest__Invokable::class);
        $queue  = Mockery::mock(ResolverTest__Invokable::class);
        $delete = Mockery::mock(ResolverTest__Invokable::class);
        $delete
            ->shouldReceive('__invoke')
            ->with($path)
            ->once()
            ->andReturns();

        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('delete')
            ->with($path)
            ->once()
            ->andReturns();

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($path)
            ->once()
            ->andReturn($path);

        $resolver->delete($path);

        self::assertEquals(
            [
                new Dependency($path, DependencyResult::Deleted),
            ],
            $dispatcher->events,
        );
    }

    public function testDeleteFile(): void {
        $path   = new FilePath('/file.txt');
        $run    = Mockery::mock(ResolverTest__Invokable::class);
        $save   = Mockery::mock(ResolverTest__Invokable::class);
        $queue  = Mockery::mock(ResolverTest__Invokable::class);
        $delete = Mockery::mock(ResolverTest__Invokable::class);
        $delete
            ->shouldReceive('__invoke')
            ->with($path)
            ->once()
            ->andReturns();

        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('delete')
            ->with($path)
            ->once()
            ->andReturns();

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($path)
            ->once()
            ->andReturn($path);

        $resolver->delete(new FileImpl($filesystem, $path));

        self::assertEquals(
            [
                new Dependency($path, DependencyResult::Deleted),
            ],
            $dispatcher->events,
        );
    }

    public function testDeleteIterable(): void {
        $aPath  = new FilePath('/a.txt');
        $bPath  = new DirectoryPath('/a/aa');
        $run    = Mockery::mock(ResolverTest__Invokable::class);
        $save   = Mockery::mock(ResolverTest__Invokable::class);
        $queue  = Mockery::mock(ResolverTest__Invokable::class);
        $delete = Mockery::mock(ResolverTest__Invokable::class);
        $delete
            ->shouldReceive('__invoke')
            ->with($aPath)
            ->once()
            ->andReturns();
        $delete
            ->shouldReceive('__invoke')
            ->with($bPath)
            ->once()
            ->andReturns();

        $container  = Mockery::mock(Container::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('delete')
            ->with($aPath)
            ->once()
            ->andReturns();
        $filesystem
            ->shouldReceive('delete')
            ->with($bPath)
            ->once()
            ->andReturns();

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($aPath)
            ->once()
            ->andReturn($aPath);
        $resolver
            ->shouldReceive('path')
            ->with($bPath)
            ->once()
            ->andReturn($bPath);

        $resolver->delete([$aPath, $bPath]);

        self::assertEquals(
            [
                new Dependency($aPath, DependencyResult::Deleted),
                new Dependency($bPath, DependencyResult::Deleted),
            ],
            $dispatcher->events,
        );
    }

    public function testSearchNull(): void {
        $run        = Mockery::mock(ResolverTest__Invokable::class);
        $save       = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $include    = ['include'];
        $exclude    = ['exclude'];
        $directory  = new DirectoryPath('directory');
        $resolved   = [new FilePath('file.txt')];
        $container  = Mockery::mock(Container::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('search')
            ->with($directory, $include, $exclude, false)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with(
                Mockery::on(static function (mixed $path): bool {
                    return $path instanceof DirectoryPath
                        && $path->equals(new DirectoryPath('.'));
                }),
            )
            ->once()
            ->andReturn($directory);

        self::assertSame($resolved, $resolver->search(include: $include, exclude: $exclude));
    }

    public function testSearchString(): void {
        $run        = Mockery::mock(ResolverTest__Invokable::class);
        $save       = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $include    = ['include'];
        $exclude    = ['exclude'];
        $directory  = new DirectoryPath('directory');
        $resolved   = [new FilePath('file.txt')];
        $container  = Mockery::mock(Container::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('search')
            ->with($directory, $include, $exclude, true)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with(
                Mockery::on(static function (mixed $path) use ($directory): bool {
                    return $path instanceof DirectoryPath
                        && $path->equals($directory);
                }),
            )
            ->once()
            ->andReturn($directory);

        self::assertNotEmpty($directory->path);
        self::assertSame($resolved, $resolver->search($directory, $include, $exclude, true));
    }

    public function testSearchDirectoryPath(): void {
        $run        = Mockery::mock(ResolverTest__Invokable::class);
        $save       = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $delete     = Mockery::mock(ResolverTest__Invokable::class);
        $include    = ['include'];
        $exclude    = ['exclude'];
        $directory  = new DirectoryPath('directory');
        $resolved   = [new FilePath('file.txt')];
        $container  = Mockery::mock(Container::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('search')
            ->with($directory, $include, $exclude, false)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [
            $container,
            $dispatcher,
            $filesystem,
            $run(...),
            $save(...),
            $queue(...),
            $delete(...),
        ]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($directory)
            ->once()
            ->andReturn($directory);

        self::assertSame($resolved, $resolver->search($directory, $include, $exclude));
    }

    #[DataProvider('dataProviderPath')]
    public function testPath(DirectoryPath|FilePath $expected, DirectoryPath|FilePath $path): void {
        $run        = (new ResolverTest__Invokable())(...);
        $save       = (new ResolverTest__Invokable())(...);
        $queue      = (new ResolverTest__Invokable())(...);
        $delete     = (new ResolverTest__Invokable())(...);
        $container  = Mockery::mock(Container::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = new class($container, $dispatcher, $filesystem, $run, $queue, $save, $delete) extends Resolver {
            #[Override]
            public function path(DirectoryPath|FilePath $path): DirectoryPath|FilePath {
                return parent::path($path);
            }

            #[Override]
            public function __get(string $name): mixed {
                return match ($name) {
                    'input'     => new DirectoryPath('/input'),
                    'output'    => new DirectoryPath('/output'),
                    'directory' => new DirectoryPath('/directory'),
                    default     => parent::__get($name),
                };
            }
        };

        self::assertEquals($expected->normalized(), $resolver->path($path));
    }

    public function testCast(): void {
        $container = Mockery::mock(Container::class);
        $container
            ->shouldReceive('make')
            ->once()
            ->andReturn(
                new ResolverTest__Cast(),
            );

        $run        = (new ResolverTest__Invokable())(...);
        $save       = (new ResolverTest__Invokable())(...);
        $queue      = (new ResolverTest__Invokable())(...);
        $delete     = (new ResolverTest__Invokable())(...);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = new Resolver($container, $dispatcher, $filesystem, $run, $save, $queue, $delete);
        $file       = new FileImpl($filesystem, new FilePath('/file.txt'));

        self::assertSame(
            $resolver->cast($file, ResolverTest__Cast::class),
            $resolver->cast($file, ResolverTest__Cast::class),
        );
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{DirectoryPath|FilePath, DirectoryPath|FilePath}>
     */
    public static function dataProviderPath(): array {
        return [
            'relative directory' => [new DirectoryPath('/directory/relative'), new DirectoryPath('relative')],
            'relative file'      => [new FilePath('/directory/file.txt'), new FilePath('file.txt')],
            'absolute directory' => [new DirectoryPath('/absolute'), new DirectoryPath('/absolute')],
            'absolute file'      => [new FilePath('/file.txt'), new FilePath('/file.txt')],
        ];
    }
    //</editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ResolverTest__Invokable {
    public function __invoke(mixed $path): void {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Cast<object>
 */
class ResolverTest__Cast implements Cast {
    #[Override]
    public function __invoke(Contract $resolver, File $file): object {
        return new class($file->path->path) {
            public function __construct(
                public string $path,
            ) {
                // empty
            }
        };
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ResolverTest__Dispatcher extends Dispatcher {
    /**
     * @var list<Event>
     */
    public array $events = [];

    public function __construct() {
        parent::__construct(null);
    }

    #[Override]
    public function __invoke(Event $event, ?UnitEnum $result = null): ?UnitEnum {
        $result         = parent::__invoke($event, $result);
        $this->events[] = $event;

        return $result;
    }
}
