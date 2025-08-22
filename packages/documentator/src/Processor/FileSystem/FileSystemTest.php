<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\Event;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModified;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileCreateFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $path         = (new FilePath(self::getTestData()->path('c.txt')))->getNormalizedPath();
        $file         = $fs->getFile($path);
        $hook         = $fs->getFile(Hook::After);
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

        self::assertSame(
            (string) (new DirectoryPath(__DIR__))->getFilePath('@.'.Hook::After->value),
            (string) $hook,
        );
    }

    public function testGetFileNotFound(): void {
        self::expectException(FileNotFound::class);

        $this->getFileSystem(__DIR__)->getFile('not found');
    }

    public function testGetFileHook(): void {
        $fs   = $this->getFileSystem(__DIR__);
        $hook = $fs->getFile(Hook::After);

        $fs->begin();

        self::assertInstanceOf(FileHook::class, $hook);
        self::assertSame($hook, $fs->getFile(Hook::After));
        self::assertSame(
            (string) (new DirectoryPath(__DIR__))->getFilePath('@.'.Hook::After->value),
            (string) $hook,
        );

        $fs->commit();

        self::assertSame($hook, $fs->getFile(Hook::After));
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
        $external = $fs->getDirectory('../Package');

        self::assertSame(
            (string) (new DirectoryPath(__DIR__))->getDirectoryPath('../../Package'),
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
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory), false)),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, '*.html'), false)),
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
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, '**/*.html'), false)),
        );

        self::assertEquals(
            [
                'c.html',
            ],
            array_map($map, iterator_to_array($filesystem->getFilesIterator($directory, '**/*.html', null, 0), false)),
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
            array_map($map, iterator_to_array($filesystem->getDirectoriesIterator($directory), false)),
        );

        self::assertEquals(
            [
                'a',
                'b',
            ],
            array_map($map, iterator_to_array($filesystem->getDirectoriesIterator($directory, depth: 0), false)),
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
                iterator_to_array($filesystem->getDirectoriesIterator($directory, exclude: '*/a'), false),
            ),
        );
    }

    public function testWriteFile(): void {
        $input      = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $path       = $input->getFilePath('file.md');
        $file       = Mockery::mock(File::class);
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

        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->never();

        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $adapter, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
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
        $content    = 'content';
        $metadata   = Mockery::mock(Metadata::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher
            ->shouldReceive('notify')
            ->never();

        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->never();

        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $adapter, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
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

        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->with((string) $path, $content)
            ->once()
            ->andReturns();

        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $adapter, $input, $input]);
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
        $content    = 'content';
        $adapter    = Mockery::mock(Adapter::class);
        $metadata   = Mockery::mock(Metadata::class);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $adapter, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
        $filesystem
            ->shouldReceive('isFile')
            ->with($path)
            ->once()
            ->andReturn(false);
        $filesystem
            ->shouldReceive('change')
            ->never();

        $metadata
            ->shouldReceive('serialize')
            ->with($path, Mockery::type(Content::class))
            ->once()
            ->andReturn($content);

        $adapter
            ->shouldReceive('write')
            ->with((string) $path, $content)
            ->once()
            ->andThrow(Exception::class);

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

        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->never();

        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $adapter, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
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

        $adapter = Mockery::mock(Adapter::class);
        $adapter
            ->shouldReceive('write')
            ->never();

        $filesystem = Mockery::mock(FileSystem::class, [$dispatcher, $metadata, $adapter, $input, $input]);
        $filesystem->shouldAllowMockingProtectedMethods();
        $filesystem->makePartial();
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

    public function testWriteHook(): void {
        self::expectException(FileNotWritable::class);

        $path = (new DirectoryPath(self::getTestData()->path('')))->getNormalizedPath();
        $fs   = $this->getFileSystem($path);
        $file = $fs->getFile(Hook::Context);

        $fs->write($file, 'hook');
    }

    public function testCache(): void {
        $fs        = $this->getFileSystem(__DIR__);
        $file      = $fs->getFile(__FILE__);
        $directory = $fs->getDirectory(__DIR__);

        self::assertSame($file, $fs->getFile(__FILE__));

        self::assertSame($directory, $fs->getDirectory(__DIR__));
    }

    /**
     * @param Closure(static): FileSystem                                          $fsFactory
     * @param Closure(static, FileSystem): (Directory|DirectoryPath|File|FilePath) $pathFactory
     */
    #[DataProvider('dataProviderGetPathname')]
    public function testGetPathname(string $expected, Closure $fsFactory, Closure $pathFactory): void {
        $fs     = $fsFactory($this);
        $path   = $pathFactory($this, $fs);
        $actual = $fs->getPathname($path);

        self::assertSame($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *      string,
     *      Closure(static): FileSystem,
     *      Closure(static, FileSystem): (Directory|DirectoryPath|File|FilePath),
     *      }>
     */
    public static function dataProviderGetPathname(): array {
        $fs        = static function (string $input, ?string $output): Closure {
            return static function (self $test) use ($input, $output): FileSystem {
                $input      = (new DirectoryPath(self::getTestData()->path($input)))->getNormalizedPath();
                $output     = $output !== null
                    ? (new DirectoryPath(self::getTestData()->path($output)))->getNormalizedPath()
                    : null;
                $filesystem = $test->getFileSystem($input, $output);

                return $filesystem;
            };
        };
        $file      = static function (FilePath|Hook|string $path): Closure {
            return static function (self $test, FileSystem $fs) use ($path): File {
                return $fs->getFile($path);
            };
        };
        $directory = static function (DirectoryPath|FilePath|string $path): Closure {
            return static function (self $test, FileSystem $fs) use ($path): Directory {
                return $fs->getDirectory($path);
            };
        };

        return [
            '(a, b): hook'                  => [
                '@ :before',
                $fs('a', 'b'),
                $file(Hook::Before),
            ],
            '(a, null): hook'               => [
                '@ :before',
                $fs('a', 'null'),
                $file(Hook::Before),
            ],
            '(a, b): in file'               => [
                '→ a.txt',
                $fs('a', 'b'),
                $file('../a/a.txt'),
            ],
            '(a, b): out file'              => [
                '← b.txt',
                $fs('a', 'b'),
                $file('../b/b.txt'),
            ],
            '(a, b): external file'         => [
                '! '.(new FilePath(self::getTestData()->path('c.txt')))->getNormalizedPath(),
                $fs('a', 'b'),
                $file('../c.txt'),
            ],
            '(a, null): in file'            => [
                '↔ a.txt',
                $fs('a', null),
                $file('../a/a.txt'),
            ],
            '(a, null): external file'      => [
                '! '.(new FilePath(self::getTestData()->path('c.txt')))->getNormalizedPath(),
                $fs('a', null),
                $file('../c.txt'),
            ],
            '(a, b): in directory'          => [
                '→ a/',
                $fs('a', 'b'),
                $directory('../a/a'),
            ],
            '(a, b): out directory'         => [
                '← b/',
                $fs('a', 'b'),
                $directory('../b/b'),
            ],
            '(a, b): external directory'    => [
                '! '.(new DirectoryPath(__DIR__))->getNormalizedPath().'/',
                $fs('a', 'b'),
                $directory(__DIR__),
            ],
            '(a, null): in directory'       => [
                '↔ a/',
                $fs('a', null),
                $directory('../a/a'),
            ],
            '(a, null): external directory' => [
                '! '.(new DirectoryPath(__DIR__))->getNormalizedPath().'/',
                $fs('a', null),
                $directory(__DIR__),
            ],
            '(a, b): relative path'         => [
                '→ a.txt',
                $fs('a', 'b'),
                static function (): FilePath {
                    return new FilePath('a.txt');
                },
            ],
            '(a, null): relative path'      => [
                '↔ a.txt',
                $fs('a', null),
                static function (): FilePath {
                    return new FilePath('a.txt');
                },
            ],
        ];
    }
    //</editor-fold>
}
