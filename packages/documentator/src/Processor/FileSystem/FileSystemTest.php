<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModified;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileCreateFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Hooks\Hook;
use LastDragon_ru\LaraASP\Documentator\Processor\Hooks\Hooks;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
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

    public function testGetFile(): void {
        $fs           = $this->getFileSystem(__DIR__);
        $path         = (new FilePath(self::getTestData()->path('c.txt')))->getNormalizedPath();
        $file         = $fs->getFile($path);
        $readonly     = $fs->getFile(__FILE__);
        $relative     = $fs->getFile(basename(__FILE__));
        $internal     = $fs->getFile(self::getTestData()->path('c.html'));
        $external     = $fs->getFile('../Processor.php');
        $fromFilePath = $fs->getFile($path);

        self::assertSame(
            (string) (new FilePath(__FILE__))->getNormalizedPath(),
            (string) $readonly,
        );

        self::assertSame(
            (string) (new FilePath(__FILE__))->getNormalizedPath(),
            (string) $relative,
        );

        self::assertSame(
            (string) (new FilePath(self::getTestData()->path('c.html')))->getNormalizedPath(),
            (string) $internal,
        );

        self::assertSame(
            (string) (new FilePath(__FILE__))->getFilePath('../Processor.php'),
            (string) $external,
        );

        self::assertEquals($file->getPath(), $fromFilePath->getPath());
        self::assertSame(
            (string) (new FilePath(self::getTestData()->path('c.txt')))->getNormalizedPath(),
            (string) $fromFilePath,
        );
    }

    public function testGetFileNotFound(): void {
        self::expectException(FileNotFound::class);

        $this->getFileSystem(__DIR__)->getFile('not found');
    }

    public function testGetDirectory(): void {
        // Prepare
        $fs = $this->getFileSystem(__DIR__.'/..');

        // Self
        self::assertSame(
            $fs->getDirectory('.'),
            $fs->getDirectory(''),
        );

        // Readonly
        $readonly = $fs->getDirectory(__DIR__);

        self::assertSame(
            (string) (new DirectoryPath(__DIR__))->getNormalizedPath(),
            (string) $readonly,
        );

        // Relative
        $relative = $fs->getDirectory(basename(__DIR__));

        self::assertSame(
            (string) (new DirectoryPath(__DIR__))->getNormalizedPath(),
            (string) $relative,
        );

        // Internal
        $internalPath = self::getTestData()->path('a');
        $internal     = $fs->getDirectory($internalPath);

        self::assertSame($internalPath, (string) $internal);

        // External
        $external = $fs->getDirectory('../Testing');

        self::assertSame(
            (string) (new DirectoryPath(__DIR__))->getDirectoryPath('../../Testing'),
            (string) $external,
        );

        // From FilePath
        $filePath     = (new FilePath(self::getTestData()->path('c.html')))->getNormalizedPath();
        $fromFilePath = $fs->getDirectory($filePath);

        self::assertSame(
            (string) (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath(),
            (string) $fromFilePath,
        );

        // From DirectoryPath
        $directoryPath     = (new DirectoryPath(self::getTestData()->path('a/a')))->getNormalizedPath();
        $fromDirectoryPath = $fs->getDirectory($directoryPath);

        self::assertSame((string) $directoryPath, (string) $fromDirectoryPath);
    }

    public function testGetDirectoryNotFound(): void {
        self::expectException(DirectoryNotFound::class);

        $this->getFileSystem(__DIR__)->getDirectory('not found');
    }

    public function testGetFilesIterator(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $filesystem = $this->getFileSystem($input);
        $directory  = $filesystem->getDirectory($input);
        $map        = static function (File $file) use ($directory): string {
            return (string) $directory->getRelativePath($file);
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
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory))),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, '*.html'))),
        );

        self::assertEquals(
            [
                'c.html',
                'c.txt',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, depth: 0))),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, '*.html', 0))),
        );

        self::assertEquals(
            [
                'a/a.html',
                'b/b.html',
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, exclude: ['#.*?\.txt$#']))),
        );
    }

    public function testGetDirectoriesIterator(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $filesystem = $this->getFileSystem($input);
        $directory  = $filesystem->getDirectory($input);
        $map        = static function (Directory $dir) use ($directory): string {
            return (string) $directory->getRelativePath($dir);
        };

        self::assertEquals(
            [
                'a',
                'a/a',
                'a/b',
                'b',
                'b/a',
                'b/b',
            ],
            array_map($map, iterator_to_array($filesystem->getDirectoriesIterator($directory))),
        );

        self::assertEquals(
            [
                'a',
                'b',
            ],
            array_map($map, iterator_to_array($filesystem->getDirectoriesIterator($directory, depth: 0))),
        );

        self::assertEquals(
            [
                'a',
                'b',
                'b/a',
                'b/b',
            ],
            array_map(
                $map,
                iterator_to_array($filesystem->getDirectoriesIterator($directory, exclude: '#^a/[^/]*?$#')),
            ),
        );

        self::assertEquals(
            [
                'a',
                'a/b',
                'b',
                'b/b',
            ],
            array_map(
                $map,
                iterator_to_array($filesystem->getDirectoriesIterator($directory, exclude: '#^[^/]*?/a$#')),
            ),
        );
    }

    public function testWriteFile(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $path       = $input->getFilePath('file.md');
        $file       = Mockery::mock(File::class);
        $hooks      = Mockery::mock(Hooks::class);
        $content    = 'content';
        $metadata   = Mockery::mock(Metadata::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->withArgs(
                static function (Event $event): bool {
                    return $event instanceof FileSystemModified
                        && $event->path === '↔ file.md'
                        && $event->type === FileSystemModifiedType::Updated;
                },
            )
            ->once()
            ->andReturns();

        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $hooks, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('save')
            ->never();
        $filesystem
            ->shouldReceive('change')
            ->with($file, $content)
            ->once()
            ->andReturns();

        $metadata
            ->shouldReceive('reset')
            ->with($file)
            ->once()
            ->andReturns();
        $metadata
            ->shouldReceive('has')
            ->with($file, Content::class)
            ->once()
            ->andReturn(false);
        $metadata
            ->shouldReceive('set')
            ->with(
                $file,
                Mockery::on(static function (mixed $value) use ($content): bool {
                    return $value instanceof Content && $value->content === $content;
                }),
            )
            ->once()
            ->andReturns();
        $metadata
            ->shouldReceive('serialize')
            ->with($path, Mockery::type(Content::class))
            ->once()
            ->andReturn($content);

        $file
            ->shouldReceive('getPath')
            ->twice()
            ->andReturn($path);

        $filesystem->write($file, $content);
    }

    public function testWriteFileNoChanges(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $path       = $input->getFilePath('file.md');
        $file       = Mockery::mock(File::class);
        $hooks      = Mockery::mock(Hooks::class);
        $content    = 'content';
        $metadata   = Mockery::mock(Metadata::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->never();

        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $hooks, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('save')
            ->never();
        $filesystem
            ->shouldReceive('change')
            ->with($file, $content)
            ->never();

        $metadata
            ->shouldReceive('reset')
            ->with($file)
            ->never();
        $metadata
            ->shouldReceive('has')
            ->with($file, Content::class)
            ->once()
            ->andReturn(true);
        $metadata
            ->shouldReceive('get')
            ->with($file, Content::class)
            ->once()
            ->andReturn(new Content($content));
        $metadata
            ->shouldReceive('set')
            ->with(
                $file,
                Mockery::on(static function (mixed $value) use ($content): bool {
                    return $value instanceof Content && $value->content === $content;
                }),
            )
            ->never();
        $metadata
            ->shouldReceive('serialize')
            ->with($path, Mockery::type(Content::class))
            ->once()
            ->andReturn($content);

        $file
            ->shouldReceive('getPath')
            ->once()
            ->andReturn($path);

        $filesystem->write($file, $content);
    }

    public function testWriteCreate(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $path       = $input->getFilePath('file.md');
        $file       = Mockery::mock(File::class);
        $hooks      = Mockery::mock(Hooks::class);
        $content    = 'content';
        $metadata   = Mockery::mock(Metadata::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->withArgs(
                static function (Event $event): bool {
                    return $event instanceof FileSystemModified
                        && $event->path === '↔ file.md'
                        && $event->type === FileSystemModifiedType::Created;
                },
            )
            ->once()
            ->andReturns();

        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $hooks, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('isFile')
            ->with($path)
            ->once()
            ->andReturn(false);
        $filesystem
            ->shouldReceive('getFile')
            ->with($path)
            ->once()
            ->andReturn($file);
        $filesystem
            ->shouldReceive('save')
            ->with($path, $content)
            ->once()
            ->andReturns();
        $filesystem
            ->shouldReceive('change')
            ->never();

        $metadata
            ->shouldReceive('reset')
            ->with($file)
            ->once()
            ->andReturns();
        $metadata
            ->shouldReceive('has')
            ->with($file, Content::class)
            ->once()
            ->andReturn(false);
        $metadata
            ->shouldReceive('set')
            ->with(
                $file,
                Mockery::on(static function (mixed $value) use ($content): bool {
                    return $value instanceof Content && $value->content === $content;
                }),
            )
            ->once()
            ->andReturns();
        $metadata
            ->shouldReceive('serialize')
            ->with($path, Mockery::type(Content::class))
            ->once()
            ->andReturn($content);

        $file
            ->shouldReceive('getPath')
            ->once()
            ->andReturn($path);

        $filesystem->write($path, $content);
    }

    public function testWriteCreateFailed(): void {
        self::expectException(FileCreateFailed::class);

        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $path       = $input->getFilePath('file.md');
        $hooks      = Mockery::mock(Hooks::class);
        $content    = 'content';
        $metadata   = Mockery::mock(Metadata::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $hooks, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('isFile')
            ->with($path)
            ->once()
            ->andReturn(false);
        $filesystem
            ->shouldReceive('save')
            ->with($path, $content)
            ->once()
            ->andThrow(Exception::class);
        $filesystem
            ->shouldReceive('change')
            ->never();

        $metadata
            ->shouldReceive('serialize')
            ->with($path, Mockery::type(Content::class))
            ->once()
            ->andReturn($content);

        $filesystem->write($path, $content);
    }

    public function testWriteOutsideOutput(): void {
        self::expectException(FileNotWritable::class);

        $path = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $fs   = $this->getFileSystem($path);
        $file = $fs->getFile(__FILE__);

        $fs->write($file, 'outside output');
    }

    public function testWriteMetadata(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $path       = $input->getFilePath('file.md');
        $file       = Mockery::mock(File::class);
        $hooks      = Mockery::mock(Hooks::class);
        $value      = new stdClass();
        $content    = 'content';
        $metadata   = Mockery::mock(Metadata::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->withArgs(
                static function (Event $event): bool {
                    return $event instanceof FileSystemModified
                        && $event->path === '↔ file.md'
                        && $event->type === FileSystemModifiedType::Updated;
                },
            )
            ->once()
            ->andReturns();

        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $hooks, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('save')
            ->never();
        $filesystem
            ->shouldReceive('change')
            ->with($file, $content)
            ->once()
            ->andReturns();

        $metadata
            ->shouldReceive('reset')
            ->with($file)
            ->once()
            ->andReturns();
        $metadata
            ->shouldReceive('has')
            ->with($file, Content::class)
            ->once()
            ->andReturn(false);
        $metadata
            ->shouldReceive('set')
            ->with(
                $file,
                Mockery::on(static function (mixed $value) use ($content): bool {
                    return $value instanceof Content && $value->content === $content;
                }),
            )
            ->once()
            ->andReturns();
        $metadata
            ->shouldReceive('set')
            ->with(
                $file,
                Mockery::on(static function (mixed $object) use ($value): bool {
                    return $value === $object;
                }),
            )
            ->once()
            ->andReturns();
        $metadata
            ->shouldReceive('serialize')
            ->with($path, $value)
            ->once()
            ->andReturn($content);
        $metadata
            ->shouldReceive('serialize')
            ->with($path, Mockery::type(Content::class))
            ->once()
            ->andReturn($content);

        $file
            ->shouldReceive('getPath')
            ->twice()
            ->andReturn($path);

        $filesystem->write($file, $value);
    }

    public function testWriteMetadataContent(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $path       = $input->getFilePath('file.md');
        $file       = Mockery::mock(File::class);
        $hooks      = Mockery::mock(Hooks::class);
        $value      = new Content('content');
        $content    = $value->content;
        $metadata   = Mockery::mock(Metadata::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->withArgs(
                static function (Event $event): bool {
                    return $event instanceof FileSystemModified
                        && $event->path === '↔ file.md'
                        && $event->type === FileSystemModifiedType::Updated;
                },
            )
            ->once()
            ->andReturns();

        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $hooks, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('save')
            ->never();
        $filesystem
            ->shouldReceive('change')
            ->with($file, $content)
            ->once()
            ->andReturns();

        $metadata
            ->shouldReceive('reset')
            ->with($file)
            ->once()
            ->andReturns();
        $metadata
            ->shouldReceive('has')
            ->with($file, Content::class)
            ->once()
            ->andReturn(false);
        $metadata
            ->shouldReceive('set')
            ->with(
                $file,
                Mockery::on(static function (mixed $value) use ($content): bool {
                    return $value instanceof Content && $value->content === $content;
                }),
            )
            ->once()
            ->andReturns();
        $metadata
            ->shouldReceive('serialize')
            ->with($path, $value)
            ->once()
            ->andReturn($content);

        $file
            ->shouldReceive('getPath')
            ->twice()
            ->andReturn($path);

        $filesystem->write($file, $value);
    }

    public function testCache(): void {
        $fs        = $this->getFileSystem(__DIR__);
        $file      = $fs->getFile(__FILE__);
        $directory = $fs->getDirectory(__DIR__);

        self::assertSame($file, $fs->getFile(__FILE__));

        self::assertSame($directory, $fs->getDirectory(__DIR__));
    }

    public function testGetPathname(): void {
        $aPath                 = (new DirectoryPath(self::getTestData()->path('a')))->getNormalizedPath();
        $bPath                 = (new DirectoryPath(self::getTestData()->path('b')))->getNormalizedPath();
        $fPath                 = (new FilePath(__FILE__))->getNormalizedPath();
        $dPath                 = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $aFileSystem           = $this->getFileSystem($aPath, $bPath);
        $aFileSystemAFile      = $aFileSystem->getFile($aPath->getFilePath('a.txt'));
        $aFileSystemADirectory = $aFileSystem->getDirectory($aPath->getDirectoryPath('a'));
        $aFileSystemBFile      = $aFileSystem->getFile($bPath->getFilePath('b.txt'));
        $aFileSystemBDirectory = $aFileSystem->getDirectory($bPath->getDirectoryPath('b'));
        $aFileSystemFFile      = $aFileSystem->getFile($fPath);
        $aFileSystemDDirectory = $aFileSystem->getDirectory($dPath);
        $aFileSystemAHook      = $aFileSystem->getHook(Hook::Before);
        $bFileSystem           = $this->getFileSystem($aPath, $aPath);
        $bFileSystemAFile      = $bFileSystem->getFile($aPath->getFilePath('a.txt'));
        $bFileSystemADirectory = $bFileSystem->getDirectory($aPath->getDirectoryPath('a'));

        self::assertSame('↔ /', $bFileSystem->getPathname($bFileSystem->input));
        self::assertSame('↔ /', $bFileSystem->getPathname($bFileSystem->output));
        self::assertSame('↔ a.txt', $bFileSystem->getPathname($bFileSystemAFile));
        self::assertSame('↔ a.txt', $bFileSystem->getPathname($bFileSystemAFile->getPath()));
        self::assertSame('→ /', $aFileSystem->getPathname($aFileSystem->input));
        self::assertSame('← /', $aFileSystem->getPathname($aFileSystem->output));
        self::assertSame('→ a.txt', $aFileSystem->getPathname($aFileSystemAFile));
        self::assertSame('→ a.txt', $aFileSystem->getPathname($aFileSystemAFile->getPath()));
        self::assertSame('← b.txt', $aFileSystem->getPathname($aFileSystemBFile));
        self::assertSame('← b.txt', $aFileSystem->getPathname($aFileSystemBFile->getPath()));
        self::assertSame("! {$fPath}", $aFileSystem->getPathname($aFileSystemFFile));
        self::assertSame("! {$fPath}", $aFileSystem->getPathname($aFileSystemFFile->getPath()));

        self::assertSame('↔ a/', $bFileSystem->getPathname($bFileSystemADirectory));
        self::assertSame('↔ a/', $bFileSystem->getPathname($bFileSystemADirectory->getPath()));
        self::assertSame('→ a/', $aFileSystem->getPathname($aFileSystemADirectory));
        self::assertSame('→ a/', $aFileSystem->getPathname($aFileSystemADirectory->getPath()));
        self::assertSame('← b/', $aFileSystem->getPathname($aFileSystemBDirectory));
        self::assertSame('← b/', $aFileSystem->getPathname($aFileSystemBDirectory->getPath()));
        self::assertSame("! {$dPath}/", $aFileSystem->getPathname($aFileSystemDDirectory));
        self::assertSame("! {$dPath}/", $aFileSystem->getPathname($aFileSystemDDirectory->getPath()));

        self::assertSame('~ :before', $aFileSystem->getPathname($aFileSystemAHook));
        self::assertSame('~ :before', $bFileSystem->getPathname($aFileSystemAHook));
    }
}
