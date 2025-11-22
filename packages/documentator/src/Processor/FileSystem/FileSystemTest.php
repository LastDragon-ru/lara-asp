<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModified;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileCreateFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotWritable;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function array_map;
use function basename;
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
        $relative     = $fs->getFile(new FilePath(basename(__FILE__)));
        $internal     = $fs->getFile(new FilePath(self::getTestData()->path('c.html')));
        $external     = $fs->getFile(new FilePath('../Processor.php'));
        $fromFilePath = $fs->getFile($path);

        self::assertSame(
            (string) (new FilePath(__FILE__))->normalized(),
            (string) $readonly,
        );

        self::assertSame(
            (string) (new FilePath(__FILE__))->normalized(),
            (string) $relative,
        );

        self::assertSame(
            (string) (new FilePath(self::getTestData()->path('c.html')))->normalized(),
            (string) $internal,
        );

        self::assertSame(
            (string) (new FilePath(__FILE__))->file('../Processor.php'),
            (string) $external,
        );

        self::assertEquals($file->path, $fromFilePath->path);
        self::assertSame(
            (string) (new FilePath(self::getTestData()->path('c.txt')))->normalized(),
            (string) $fromFilePath,
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

    public function testWriteFile(): void {
        $content = 'content';
        $input   = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path    = $input->file('file.md');
        $file    = Mockery::mock(File::class, [$path, Mockery::mock(Caster::class)]);

        $caster = Mockery::mock(Caster::class);
        $caster
            ->shouldReceive('castFrom')
            ->with(
                $file,
                Mockery::on(static function (mixed $value) use ($content): bool {
                    return $value instanceof Content && $value->content === $content;
                }),
            )
            ->once()
            ->andReturns($content);

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

        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->never();

        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('change')
            ->with($file, $content)
            ->once()
            ->andReturns();

        $filesystem->write($file, $content);
    }

    public function testWriteFileNoChanges(): void {
        $content = 'content';
        $input   = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path    = $input->file('file.md');
        $file    = Mockery::mock(File::class, [$path, Mockery::mock(Caster::class)]);

        $caster = Mockery::mock(Caster::class);
        $caster
            ->shouldReceive('castFrom')
            ->with($file, Mockery::type(Content::class))
            ->once()
            ->andReturn(null);

        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->never();

        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->never();

        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('change')
            ->never();

        $filesystem->write($file, $content);
    }

    public function testWriteCreate(): void {
        $input   = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path    = $input->file('file.md');
        $content = 'content';
        $caster  = Mockery::mock(Caster::class);
        $caster
            ->shouldReceive('castFrom')
            ->with(
                Mockery::type(File::class),
                Mockery::on(static function (mixed $value) use ($content): bool {
                    return $value instanceof Content && $value->content === $content;
                }),
            )
            ->once()
            ->andReturns($content);

        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->withArgs(
                static function (Event $event) use ($path): bool {
                    return $event instanceof FileSystemModified
                        && $event->path === $path
                        && $event->type === FileSystemModifiedType::Created;
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
            ->shouldReceive('change')
            ->never();

        $filesystem->write($path, $content);
    }

    public function testWriteCreateFailed(): void {
        self::expectException(FileCreateFailed::class);

        $input   = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path    = $input->file('file.md');
        $content = 'content';
        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->with((string) $path, $content)
            ->once()
            ->andThrow(Exception::class);

        $caster = Mockery::mock(Caster::class);
        $caster
            ->shouldReceive('castFrom')
            ->with(
                Mockery::type(File::class),
                Mockery::on(static function (mixed $value) use ($content): bool {
                    return $value instanceof Content && $value->content === $content;
                }),
            )
            ->once()
            ->andReturns($content);

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
            ->shouldReceive('change')
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
        $content = 'content';
        $value   = new stdClass();
        $input   = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path    = $input->file('file.md');
        $file    = Mockery::mock(File::class, [$path, Mockery::mock(Caster::class)]);

        $caster = Mockery::mock(Caster::class);
        $caster
            ->shouldReceive('castFrom')
            ->with($file, $value)
            ->once()
            ->andReturn($content);

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

        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->never();

        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('change')
            ->with($file, $content)
            ->once()
            ->andReturns();

        $filesystem->write($file, $value);
    }

    public function testWriteContent(): void {
        $content = 'content';
        $value   = new Content($content);
        $input   = (new DirectoryPath(self::getTestData()->path('')))->normalized();
        $path    = $input->file('file.md');
        $file    = Mockery::mock(File::class, [$path, Mockery::mock(Caster::class)]);

        $caster = Mockery::mock(Caster::class);
        $caster
            ->shouldReceive('castFrom')
            ->with($file, $value)
            ->once()
            ->andReturn($content);

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

        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->never();

        $filesystem = Mockery::mock(FileSystem::class, [$adapter, $dispatcher, $caster, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('change')
            ->with($file, $content)
            ->once()
            ->andReturns();

        $filesystem->write($file, $value);
    }

    public function testCache(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $file = $fs->getFile(new FilePath(__FILE__));

        self::assertSame($file, $fs->getFile(new FilePath(__FILE__)));
    }
    // </editor-fold>
}
