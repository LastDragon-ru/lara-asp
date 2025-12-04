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
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileReadFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileSaveFailed;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

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
    public function testGetFile(): void {
        $fs           = $this->getFileSystem(__DIR__);
        $path         = (new FilePath(self::getTestData()->path('c.txt')))->normalized();
        $file         = $fs->getFile($path);
        $readonly     = $fs->getFile(new FilePath(__FILE__));
        $relative     = $fs->getFile(new FilePath($readonly->name));
        $internal     = $fs->getFile(new FilePath(self::getTestData()->path('c.html')));
        $external     = $fs->getFile(new FilePath('../Processor.php'));
        $fromFilePath = $fs->getFile($path);

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

        self::assertSame(
            (string) (new FilePath(__FILE__))->file('../Processor.php'),
            (string) $external->path,
        );

        self::assertEquals($file->path, $fromFilePath->path);
        self::assertSame(
            (string) (new FilePath(self::getTestData()->path('c.txt')))->normalized(),
            (string) $fromFilePath->path,
        );
    }

    public function testGetFileNotFound(): void {
        self::expectException(FileNotFound::class);

        $this->getFileSystem(__DIR__)->getFile(new FilePath('not found'));
    }

    public function testGetFilesIterator(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $filesystem = $this->getFileSystem($input);
        $directory  = $input;
        $map        = static function (File $file) use ($directory): string {
            return (string) $directory->relative($file->path);
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
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory), false)),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, ['*.html']), false)),
        );

        self::assertEquals(
            [
                'c.html',
                'c.txt',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, depth: 0), false)),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, ['**/*.html']), false)),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, ['**/*.html'], [], 0), false)),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map(
                $map,
                iterator_to_array($filesystem->getFilesIterator($directory, exclude: ['*.txt', '**/**/*.txt']), false),
            ),
        );
    }

    public function testGetFilesIteratorDirectoryNotFound(): void {
        self::expectException(DirectoryNotFound::class);

        iterator_to_array(
            $this->getFileSystem(__DIR__)->getFilesIterator(new DirectoryPath('not found')),
        );
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

        $file = Mockery::mock(File::class, [$filesystem, $path, Mockery::mock(Caster::class)]);

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

        self::expectException(FileReadFailed::class);

        $file = Mockery::mock(File::class, [$filesystem, $path, Mockery::mock(Caster::class)]);

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

        $file = Mockery::mock(File::class, [$filesystem, $path, Mockery::mock(Caster::class)]);

        $filesystem
            ->shouldReceive('queue')
            ->with($file)
            ->once()
            ->andReturns();

        self::assertSame($file, $filesystem->write($file, $content));
        self::assertSame($content, $filesystem->read($file));
    }

    public function testWriteFileNoChanges(): void {
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
        $file = Mockery::mock(File::class, [$filesystem, $path, Mockery::mock(Caster::class)]);

        $caster
            ->shouldReceive('castFrom')
            ->with($file, Mockery::type(stdClass::class))
            ->once()
            ->andReturn(null);

        $filesystem->write($file, new stdClass());
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
            ->shouldReceive('isFile')
            ->with($path)
            ->once()
            ->andReturn(false);
        $filesystem
            ->shouldReceive('getFile')
            ->never();
        $filesystem
            ->shouldReceive('queue')
            ->never();

        $file = $filesystem->write($path, $content);

        self::assertSame($path, $file->path);
        self::assertSame($content, $filesystem->read($file));
    }

    public function testWriteCreateFailed(): void {
        self::expectException(FileSaveFailed::class);

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
            ->shouldReceive('isFile')
            ->with($path)
            ->once()
            ->andReturn(false);
        $filesystem
            ->shouldReceive('getFile')
            ->never();
        $filesystem
            ->shouldReceive('queue')
            ->never();

        $filesystem->write($path, $content);
    }

    public function testWriteOutsideOutput(): void {
        self::expectException(FileNotWritable::class);

        $path = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $fs   = $this->getFileSystem($path);
        $file = $fs->getFile(new FilePath(__FILE__));

        $fs->write($file, 'outside output');
    }

    public function testWriteObject(): void {
        $input  = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path   = $input->file('file.md');
        $caster = Mockery::mock(Caster::class);

        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->withArgs(
                static function (Event $event) use ($path): bool {
                    return $event instanceof FileSystemModified
                        && $event->type === FileSystemModifiedType::Updated
                        && $event->path->equals($path);
                },
            )
            ->once()
            ->andReturns();

        $adapter    = Mockery::mock(Adapter::class);
        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();

        $content = 'content';
        $value   = new stdClass();
        $input   = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path    = $input->file('file.md');
        $file    = Mockery::mock(File::class, [$filesystem, $path, Mockery::mock(Caster::class)]);

        $caster
            ->shouldReceive('castFrom')
            ->with($file, $value)
            ->once()
            ->andReturn($content);

        $filesystem
            ->shouldReceive('queue')
            ->with($file)
            ->once()
            ->andReturns();

        self::assertSame($file, $filesystem->write($file, $value));
        self::assertSame($content, $filesystem->read($file));
    }

    public function testCache(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $file = $fs->getFile(new FilePath(__FILE__));

        self::assertSame($file, $fs->getFile(new FilePath(__FILE__)));
    }

    public function testPropertyDirectory(): void {
        $fs = $this->getFileSystem(__DIR__);
        $a  = new DirectoryPath('/a');
        $b  = new DirectoryPath('/b');

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
    // </editor-fold>
}
