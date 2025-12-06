<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Resolver::class)]
final class ResolverTest extends TestCase {
    public function testGet(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $filepath   = new FilePath('file.txt');
        $resolved   = Mockery::mock(File::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($resolved, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();

        self::assertSame($resolved, $resolver->get($filepath));
    }

    public function testGetString(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $filepath   = new FilePath('file.txt');
        $resolved   = Mockery::mock(File::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath->path)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($resolved, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();

        self::assertSame($resolved, $resolver->get($filepath->path));
    }

    public function testGetException(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->never();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $filepath   = new FilePath('file.txt');
        $exception  = new Exception();
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andThrow($exception);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($filepath, DependencyResolvedResult::Failed)
            ->once()
            ->andReturns();

        self::expectExceptionObject($exception);

        $resolver->get($filepath);
    }

    public function testFind(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $filepath   = new FilePath('file.txt');
        $resolved   = Mockery::mock(File::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
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

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($resolved, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();

        self::assertSame($resolved, $resolver->find($filepath));
    }

    public function testFindString(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $filepath   = new FilePath('file.txt');
        $resolved   = Mockery::mock(File::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
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

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath->path)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($resolved, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();

        self::assertSame($resolved, $resolver->find($filepath->path));
    }

    public function testFindNotFound(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->never();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $filepath   = new FilePath('file.txt');
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('exists')
            ->with($filepath)
            ->once()
            ->andReturn(false);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($filepath, DependencyResolvedResult::Null)
            ->once()
            ->andReturns();

        self::assertNull($resolver->find($filepath));
    }

    public function testFindException(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->never();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $filepath   = new FilePath('file.txt');
        $exception  = new Exception();
        $dispatcher = Mockery::mock(Dispatcher::class);
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

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($filepath, DependencyResolvedResult::Failed)
            ->once()
            ->andReturns();

        self::expectExceptionObject($exception);

        $resolver->find($filepath);
    }

    public function testSave(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $content    = 'content';
        $filepath   = new FilePath('file.txt');
        $resolved   = Mockery::mock(File::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('write')
            ->with($filepath, $content)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath, true)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($resolved, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();

        self::assertSame($resolved, $resolver->save($filepath, $content));
    }

    public function testSaveException(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->never();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $content    = 'content';
        $filepath   = new FilePath('file.txt');
        $exception  = new Exception();
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('write')
            ->with($filepath, $content)
            ->once()
            ->andThrow($exception);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath, true)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($filepath, DependencyResolvedResult::Failed)
            ->once()
            ->andReturns();

        self::expectExceptionObject($exception);

        $resolver->save($filepath, $content);
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

        $filepath   = new FilePath('/file.txt');
        $resolved   = Mockery::mock(File::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run, Closure::fromCallable($queue)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($resolved, DependencyResolvedResult::Queued)
            ->once()
            ->andReturns();

        $resolver->queue($filepath);
    }

    public function testQueueString(): void {
        $run   = static function (): void {
            // empty
        };
        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->once()
            ->andReturns();

        $filepath   = new FilePath('/file.txt');
        $resolved   = Mockery::mock(File::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run, Closure::fromCallable($queue)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($filepath->path)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($resolved, DependencyResolvedResult::Queued)
            ->once()
            ->andReturns();

        $resolver->queue($filepath->path);
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

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run, Closure::fromCallable($queue)]);
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

    public function testSearchNull(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->never();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $depth      = 5;
        $include    = ['include'];
        $exclude    = ['exclude'];
        $directory  = new DirectoryPath('directory');
        $resolved   = [new FilePath('file.txt')];
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('search')
            ->with($directory, $include, $exclude, $depth)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
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

        self::assertSame($resolved, $resolver->search($include, $exclude, $depth));
    }

    public function testSearchString(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->never();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $depth      = 5;
        $include    = ['include'];
        $exclude    = ['exclude'];
        $directory  = new DirectoryPath('directory');
        $resolved   = [new FilePath('file.txt')];
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('search')
            ->with($directory, $include, $exclude, $depth)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
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
        self::assertSame($resolved, $resolver->search($include, $exclude, $depth, $directory->path));
    }

    public function testSearchDirectoryPath(): void {
        $run = Mockery::mock(ResolverTest__Invokable::class);
        $run
            ->shouldReceive('__invoke')
            ->never();

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $depth      = 5;
        $include    = ['include'];
        $exclude    = ['exclude'];
        $directory  = new DirectoryPath('directory');
        $resolved   = [new FilePath('file.txt')];
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('search')
            ->with($directory, $include, $exclude, $depth)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($directory)
            ->once()
            ->andReturn($directory);

        self::assertSame($resolved, $resolver->search($include, $exclude, $depth, $directory));
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

        $file = Mockery::mock(File::class, [$filesystem, $filepath, Mockery::mock(Caster::class)]);

        $resolver->notify($file, DependencyResolvedResult::Missed);
        $resolver->notify($path, DependencyResolvedResult::Success);
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
