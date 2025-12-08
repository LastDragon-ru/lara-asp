<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolved;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyResolvedResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

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
            ->with($filepath, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();

        self::assertSame($resolved, $resolver->get($filepath));
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

        self::expectExceptionObject(new DependencyUnresolvable($filepath, $exception));

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
            ->with($filepath, DependencyResolvedResult::Success)
            ->once()
            ->andReturns();

        self::assertSame($resolved, $resolver->find($filepath));
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

        self::expectExceptionObject(new DependencyUnresolvable($filepath, $exception));

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
            ->with($filepath)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($filepath, DependencyResolvedResult::Success)
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
            ->with($filepath)
            ->once()
            ->andReturn($filepath);
        $resolver
            ->shouldReceive('notify')
            ->with($filepath, DependencyResolvedResult::Failed)
            ->once()
            ->andReturns();

        self::expectExceptionObject(new DependencyUnresolvable($filepath, $exception));

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
            ->with($filepath, DependencyResolvedResult::Queued)
            ->once()
            ->andReturns();

        $resolver->queue($filepath);
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
            ->with($aPath, DependencyResolvedResult::Queued)
            ->once()
            ->andReturns();
        $resolver
            ->shouldReceive('notify')
            ->with($bPath, DependencyResolvedResult::Queued)
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

    public function testNotify(): void {
        $path       = new FilePath('path/to/file.txt');
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

        $callback = static function (File $file): void {
            // empty
        };
        $resolver = Mockery::mock(Resolver::class, [$dispatcher, $filesystem, $callback, $callback]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();

        $resolver->notify($path, DependencyResolvedResult::Success);
    }

    /**
     * @param DirectoryPath|FilePath|non-empty-string $path
     */
    #[DataProvider('dataProviderPath')]
    public function testPath(DirectoryPath|FilePath $expected, DirectoryPath|FilePath|string $path): void {
        $run        = (new ResolverTest__Invokable())(...);
        $queue      = (new ResolverTest__Invokable())(...);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = new class($dispatcher, $filesystem, $run, $queue) extends Resolver {
            #[Override]
            public function path(DirectoryPath|FilePath|string $path): DirectoryPath|FilePath {
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
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{DirectoryPath|FilePath, DirectoryPath|FilePath|non-empty-string}>
     */
    public static function dataProviderPath(): array {
        return [
            'relative + directory' => [new DirectoryPath('/directory/relative'), new DirectoryPath('relative')],
            'relative + file'      => [new FilePath('/directory/file.txt'), new FilePath('file.txt')],
            'relative + string'    => [new FilePath('/directory/file.txt'), 'file.txt'],
            'output + directory'   => [new DirectoryPath('/output/relative'), new DirectoryPath('~output/relative')],
            'output + file'        => [new FilePath('/output/file.txt'), new FilePath('~output/file.txt')],
            'output + string'      => [new FilePath('/output/file.txt'), '~output/file.txt'],
            'input + directory'    => [new DirectoryPath('/input/relative'), new DirectoryPath('~input/relative')],
            'input + file'         => [new FilePath('/input/file.txt'), new FilePath('~input/file.txt')],
            'input + string'       => [new FilePath('/input/file.txt'), '~input/file.txt'],
            'absolute'             => [new FilePath('/file.txt'), '/file.txt'],
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
    public function __invoke(File $file): void {
        // empty
    }
}
