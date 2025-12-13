<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModified;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathReadFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathWriteFailed;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(FileSystem::class)]
final class FileSystemTest extends TestCase {
    use WithProcessor;

    // <editor-fold desc="Tests">
    // =========================================================================
    public function testExists(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $base = (new DirectoryPath(self::getTestData()->path('')))->normalized();

        self::assertTrue($fs->exists($base->file('c.txt')));
        self::assertFalse($fs->exists($base->file('404.txt')));
    }

    public function testGet(): void {
        $fs           = $this->getFileSystem(__DIR__);
        $path         = (new FilePath(self::getTestData()->path('c.txt')))->normalized();
        $file         = $fs->get($path);
        $readonly     = $fs->get($fs->input->file(__FILE__));
        $relative     = $fs->get($fs->input->resolve(new FilePath($readonly->name)));
        $internal     = $fs->get(new FilePath(self::getTestData()->path('c.html')));
        $fromFilePath = $fs->get($path);

        self::assertSame(
            (string) (new FilePath(__FILE__))->normalized(),
            (string) $readonly->path,
        );

        self::assertSame(
            (string) (new FilePath(__FILE__))->normalized(),
            (string) $relative->path,
        );

        self::assertSame(
            (string) (new FilePath(self::getTestData()->path('c.html')))->normalized(),
            (string) $internal->path,
        );

        self::assertEquals($file->path, $fromFilePath->path);
        self::assertSame(
            (string) (new FilePath(self::getTestData()->path('c.txt')))->normalized(),
            (string) $fromFilePath->path,
        );
    }

    public function testGetFileExternal(): void {
        self::expectException(PathUnavailable::class);

        $this
            ->getFileSystem(__DIR__)
            ->get(new FilePath('../Processor.php'));
    }

    public function testGetFileNotFound(): void {
        self::expectException(PathNotFound::class);

        $fs   = $this->getFileSystem(__DIR__);
        $file = $fs->input->resolve(new FilePath('not found'));

        $fs->get($file);
    }

    public function testSearch(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $filesystem = $this->getFileSystem($input);
        $directory  = $input;
        $map        = static function (FilePath $path) use ($directory): string {
            return (string) $directory->relative($path);
        };

        self::assertEquals(
            [
                'a/a.html',
                'a/a.txt',
                'a/a/aa.txt',
                'a/b/ab.txt',
                'b/a/ba.txt',
                'b/b.html',
                'b/b.txt',
                'b/b/bb.txt',
                'c.html',
                'c.txt',
            ],
            array_map($map, iterator_to_array($filesystem->search($directory), false)),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->search($directory, ['*.html']), false)),
        );

        self::assertEquals(
            [
                'c.html',
                'c.txt',
            ],
            array_map($map, iterator_to_array($filesystem->search($directory, depth: 0), false)),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->search($directory, ['**/*.html']), false)),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->search($directory, ['**/*.html'], [], 0), false)),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map(
                $map,
                iterator_to_array($filesystem->search($directory, exclude: ['*.txt', '**/**/*.txt']), false),
            ),
        );
    }

    public function testSearchDirectoryNotFound(): void {
        self::expectException(PathNotFound::class);

        $fs        = $this->getFileSystem(__DIR__);
        $directory = $fs->input->resolve(new DirectoryPath('not found'));

        iterator_to_array($fs->search($directory));
    }

    public function testRead(): void {
        $content = 'content';
        $input   = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path    = $input->file('file.md');
        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('read')
            ->with($path)
            ->once()
            ->andReturn($content);

        $caster     = Mockery::mock(Caster::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();

        $file = Mockery::mock(File::class, [$filesystem, $caster, $path]);

        self::assertSame($content, $filesystem->read($file));
        self::assertSame($content, $filesystem->read($file)); // should be cached
    }

    public function testReadError(): void {
        $input   = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path    = $input->file('file.md');
        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('read')
            ->with($path)
            ->once()
            ->andThrow(new Exception());

        $caster     = Mockery::mock(Caster::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();

        self::expectException(PathReadFailed::class);

        $file = Mockery::mock(File::class, [$filesystem, $caster, $path]);

        $filesystem->read($file);
    }

    public function testWriteFile(): void {
        $content    = 'content';
        $input      = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path       = $input->file('file.md');
        $caster     = Mockery::mock(Caster::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->withArgs(
                static function (Event $event) use ($path): bool {
                    return $event instanceof FileSystemModified
                        && $event->path === $path
                        && $event->type === FileSystemModifiedType::Updated;
                },
            )
            ->once()
            ->andReturns();

        $adapter    = Mockery::mock(Adapter::class);
        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();

        $file = Mockery::mock(File::class, [$filesystem, $caster, $path]);

        $filesystem
            ->shouldReceive('queue')
            ->with($file)
            ->once()
            ->andReturns();

        self::assertSame($file, $filesystem->write($file, $content));
        self::assertSame($content, $filesystem->read($file));
    }

    public function testWriteFileNoChanges(): void {
        $content    = 'content';
        $input      = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $caster     = Mockery::mock(Caster::class);
        $adapter    = Mockery::mock(Adapter::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('queue')
            ->never();

        $path = $input->file('file.md');
        $file = Mockery::mock(File::class, [$filesystem, $caster, $path]);

        $adapter
            ->shouldReceive('read')
            ->with($path)
            ->once()
            ->andReturn($content);

        $filesystem->read($file);
        $filesystem->write($file, $content);
    }

    public function testWriteCreate(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path       = $input->file('file.md');
        $content    = 'content';
        $caster     = Mockery::mock(Caster::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->withArgs(
                static function (Event $event) use ($path): bool {
                    return $event instanceof FileSystemModified
                        && $event->type === FileSystemModifiedType::Created
                        && $event->path->equals($path);
                },
            )
            ->once()
            ->andReturns();

        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->with((string) $path, $content)
            ->once()
            ->andReturns();

        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('exists')
            ->with($path)
            ->once()
            ->andReturn(false);
        $filesystem
            ->shouldReceive('get')
            ->never();
        $filesystem
            ->shouldReceive('queue')
            ->never();

        $file = $filesystem->write($path, $content);

        self::assertSame($path, $file->path);
        self::assertSame($content, $filesystem->read($file));
    }

    public function testWriteCreateFailed(): void {
        self::expectException(PathWriteFailed::class);

        $input   = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path    = $input->file('file.md');
        $content = 'content';
        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->with((string) $path, $content)
            ->once()
            ->andThrow(Exception::class);

        $caster     = Mockery::mock(Caster::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('exists')
            ->with($path)
            ->once()
            ->andReturn(false);
        $filesystem
            ->shouldReceive('get')
            ->never();
        $filesystem
            ->shouldReceive('queue')
            ->never();

        $filesystem->write($path, $content);
    }

    public function testWriteExternal(): void {
        self::expectException(PathUnavailable::class);

        $this
            ->getFileSystem(__DIR__)
            ->write(new FilePath('../Processor.php'), 'external');
    }

    public function testWriteOutsideOutput(): void {
        self::expectException(PathNotWritable::class);

        $base   = new DirectoryPath(__DIR__);
        $input  = $base->directory('input');
        $output = $base->directory('output');

        $this
            ->getFileSystem($input, $output)
            ->write($input->file('file.txt'), 'external');
    }

    public function testCache(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $file = $fs->get($fs->input->file(__FILE__));

        self::assertSame($file, $fs->get($fs->input->file(__FILE__)));
    }

    public function testPropertyDirectory(): void {
        $fs = $this->getFileSystem(__DIR__);
        $a  = $fs->input->directory('a');
        $b  = $fs->input->directory('b');

        self::assertSame($fs->input, $fs->directory);

        $fs->begin($a);

        self::assertSame($a, $fs->directory);

        $fs->begin($b);

        self::assertSame($b, $fs->directory);

        $fs->commit();

        self::assertSame($a, $fs->directory);

        $fs->commit();

        self::assertSame($fs->input, $fs->directory);
    }

    #[DataProvider('dataProviderPath')]
    public function testPath(Exception|DirectoryPath|FilePath $expected, DirectoryPath|FilePath $path): void {
        $dispatcher = Mockery::mock(Dispatcher::class);
        $adapter    = Mockery::mock(Adapter::class);
        $caster     = Mockery::mock(Caster::class);
        $output     = new DirectoryPath('/output');
        $input      = new DirectoryPath('/input');
        $fs         = new class($adapter, $dispatcher, $caster, $input, $output) extends FileSystem {
            #[Override]
            public function path(DirectoryPath|FilePath $path, ?DirectoryPath $base = null): DirectoryPath|FilePath {
                return parent::path($path, $base);
            }
        };

        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $actual = $fs->path($path);

        self::assertInstanceOf($expected::class, $actual);
        self::assertNotInstanceOf(Exception::class, $expected);
        self::assertSame($expected->path, $actual->path);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|DirectoryPath|FilePath, DirectoryPath|FilePath}>
     */
    public static function dataProviderPath(): array {
        return [
            'relative'      => [
                new PathUnavailable(new FilePath('file.txt')),
                new FilePath('file.txt'),
            ],
            'external'      => [
                new PathUnavailable(new FilePath('/file.txt')),
                new FilePath('/file.txt'),
            ],
            'inside input'  => [
                new FilePath('/input/file.txt'),
                new FilePath('/input/file.txt'),
            ],
            'inside output' => [
                new DirectoryPath('/output/file.txt'),
                new DirectoryPath('/output/file.txt'),
            ],
        ];
    }
    // </editor-fold>
}
