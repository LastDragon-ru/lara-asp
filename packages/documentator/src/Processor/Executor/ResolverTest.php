<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver as Contract;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\DependencyEnd;
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

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $filepath   = new FilePath('file.txt');
        $resolved   = Mockery::mock(File::class);
        $container  = Mockery::mock(ContainerResolver::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run(...), $queue(...)]);
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
                new DependencyBegin($filepath),
                new DependencyEnd(DependencyResult::Resolved),
            ],
            $dispatcher->events,
        );
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
        $container  = Mockery::mock(ContainerResolver::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andThrow($exception);

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run(...), $queue(...)]);
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
                    new DependencyBegin($filepath),
                    new DependencyEnd(DependencyResult::Error),
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

        $queue = Mockery::mock(ResolverTest__Invokable::class);
        $queue
            ->shouldReceive('__invoke')
            ->never();

        $filepath   = new FilePath('file.txt');
        $resolved   = Mockery::mock(File::class);
        $container  = Mockery::mock(ContainerResolver::class);
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

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run(...), $queue(...)]);
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
                new DependencyBegin($filepath),
                new DependencyEnd(DependencyResult::Resolved),
            ],
            $dispatcher->events,
        );
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
        $container  = Mockery::mock(ContainerResolver::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('exists')
            ->with($filepath)
            ->once()
            ->andReturn(false);

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run(...), $queue(...)]);
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
                new DependencyBegin($filepath),
                new DependencyEnd(DependencyResult::NotFound),
            ],
            $dispatcher->events,
        );
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
        $container  = Mockery::mock(ContainerResolver::class);
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

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run(...), $queue(...)]);
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
                    new DependencyBegin($filepath),
                    new DependencyEnd(DependencyResult::Error),
                ],
                $dispatcher->events,
            );
        }
    }

    public function testSave(): void {
        $run        = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $container  = Mockery::mock(ContainerResolver::class);
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

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run(...), $queue(...)]);
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
                new DependencyBegin($filepath),
                new DependencyEnd(DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
    }

    public function testSaveException(): void {
        $run        = Mockery::mock(ResolverTest__Invokable::class);
        $queue      = Mockery::mock(ResolverTest__Invokable::class);
        $content    = 'content';
        $filepath   = new FilePath('file.txt');
        $exception  = new Exception();
        $container  = Mockery::mock(ContainerResolver::class);
        $dispatcher = $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('write')
            ->with($filepath, $content)
            ->once()
            ->andThrow($exception);

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run(...), $queue(...)]);
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
                new DependencyBegin($filepath),
                new DependencyEnd(DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
    }

    public function testSaveCastReset(): void {
        $dispatcher = new ResolverTest__Dispatcher();
        $container  = Mockery::mock(ContainerResolver::class);
        $container
            ->shouldReceive('getInstance')
            ->once()
            ->andReturn($this->app());

        $run        = (new ResolverTest__Invokable())(...);
        $queue      = (new ResolverTest__Invokable())(...);
        $content    = 'content';
        $filepath   = new FilePath('/file.txt');
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = new Resolver($container, $dispatcher, $filesystem, $run, $queue);
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
                new DependencyBegin($filepath),
                new DependencyEnd(DependencyResult::Saved),
            ],
            $dispatcher->events,
        );
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
        $container  = Mockery::mock(ContainerResolver::class);
        $dispatcher = new ResolverTest__Dispatcher();
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('get')
            ->with($filepath)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run, $queue(...)]);
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
                new DependencyBegin($filepath),
                new DependencyEnd(DependencyResult::Queued),
            ],
            $dispatcher->events,
        );
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

        $container  = Mockery::mock(ContainerResolver::class);
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

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run, $queue(...)]);
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
                new DependencyBegin($aPath),
                new DependencyEnd(DependencyResult::Queued),
                new DependencyBegin($bPath),
                new DependencyEnd(DependencyResult::Queued),
            ],
            $dispatcher->events,
        );
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

        $include    = ['include'];
        $exclude    = ['exclude'];
        $directory  = new DirectoryPath('directory');
        $resolved   = [new FilePath('file.txt')];
        $container  = Mockery::mock(ContainerResolver::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('search')
            ->with($directory, $include, $exclude)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run(...), $queue(...)]);
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

        self::assertSame($resolved, $resolver->search($include, $exclude));
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

        $include    = ['include'];
        $exclude    = ['exclude'];
        $directory  = new DirectoryPath('directory');
        $resolved   = [new FilePath('file.txt')];
        $container  = Mockery::mock(ContainerResolver::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('search')
            ->with($directory, $include, $exclude)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run(...), $queue(...)]);
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
        self::assertSame($resolved, $resolver->search($include, $exclude, $directory));
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

        $include    = ['include'];
        $exclude    = ['exclude'];
        $directory  = new DirectoryPath('directory');
        $resolved   = [new FilePath('file.txt')];
        $container  = Mockery::mock(ContainerResolver::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $filesystem
            ->shouldReceive('search')
            ->with($directory, $include, $exclude)
            ->once()
            ->andReturn($resolved);

        $resolver = Mockery::mock(Resolver::class, [$container, $dispatcher, $filesystem, $run(...), $queue(...)]);
        $resolver->shouldAllowMockingProtectedMethods();
        $resolver->makePartial();
        $resolver
            ->shouldReceive('path')
            ->with($directory)
            ->once()
            ->andReturn($directory);

        self::assertSame($resolved, $resolver->search($include, $exclude, $directory));
    }

    #[DataProvider('dataProviderPath')]
    public function testPath(DirectoryPath|FilePath $expected, DirectoryPath|FilePath $path): void {
        $run        = (new ResolverTest__Invokable())(...);
        $queue      = (new ResolverTest__Invokable())(...);
        $container  = Mockery::mock(ContainerResolver::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = new class($container, $dispatcher, $filesystem, $run, $queue) extends Resolver {
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
        $container = Mockery::mock(ContainerResolver::class);
        $container
            ->shouldReceive('getInstance')
            ->once()
            ->andReturn($this->app());

        $run        = (new ResolverTest__Invokable())(...);
        $queue      = (new ResolverTest__Invokable())(...);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class);
        $resolver   = new Resolver($container, $dispatcher, $filesystem, $run, $queue);
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
    public function __invoke(File $file): void {
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
