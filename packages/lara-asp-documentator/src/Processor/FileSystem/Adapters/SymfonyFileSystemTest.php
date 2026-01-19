<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\PhpUnit\Utils\TempFile;
use PHPUnit\Framework\Attributes\CoversClass;

use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function sort;

/**
 * @internal
 */
#[CoversClass(SymfonyFileSystem::class)]
final class SymfonyFileSystemTest extends TestCase {
    public function testExists(): void {
        $adapter = new SymfonyFileSystem();

        self::assertTrue($adapter->exists(new FilePath(__FILE__)));
        self::assertFalse($adapter->exists(new FilePath(__DIR__)));
        self::assertFalse($adapter->exists(new DirectoryPath(__FILE__)));
        self::assertTrue($adapter->exists(new DirectoryPath(__DIR__)));
    }

    public function testSearch(): void {
        $path    = new DirectoryPath(self::getTestData()->path(''));
        $adapter = new SymfonyFileSystem();

        self::assertSame(
            [
                'a.txt',
                'a/',
                'a/aa.txt',
                'a/aa/',
                'a/aa/aaa.txt',
                'b.txt',
                'b/',
                'b/bb.txt',
                'b/bb/',
                'b/bb/bbb.txt',
            ],
            $this->asArray($path, $adapter->search($path)),
        );
        self::assertSame(
            [
                'a.txt',
                'b.txt',
                'b/',
                'b/bb.txt',
                'b/bb/',
                'b/bb/bbb.txt',
            ],
            $this->asArray($path, $adapter->search($path, exclude: ['a/**/*.txt', 'a/**/', 'a/'])),
        );
        self::assertSame(
            [
                'a.txt',
            ],
            $this->asArray($path, $adapter->search($path, include: ['a.txt'])),
        );
        self::assertSame(
            [
                'a.txt',
                'a/aa.txt',
                'a/aa/aaa.txt',
                'b.txt',
                'b/bb.txt',
                'b/bb/bbb.txt',
            ],
            $this->asArray($path, $adapter->search($path, include: ['**/*.txt'])),
        );
        self::assertSame(
            [
                'a.txt',
                'a/aa.txt',
                'b.txt',
                'b/bb.txt',
                'b/bb/bbb.txt',
            ],
            $this->asArray($path, $adapter->search($path, include: ['**/*.txt'], exclude: ['**/aa/*'])),
        );
        self::assertSame(
            [
                'a/aa.txt',
                'a/aa/aaa.txt',
            ],
            $this->asArray($path, $adapter->search($path, include: ['a/**/*.txt'])),
        );
        self::assertSame(
            [
                'a/.a.txt',
                'a/aa.txt',
                'a/aa/aaa.txt',
            ],
            $this->asArray($path, $adapter->search($path, include: ['a/**/*.txt'], hidden: true)),
        );
        self::assertSame(
            [
                'b/bb/',
            ],
            $this->asArray($path, $adapter->search($path, include: ['b/*/'])),
        );
        self::assertSame(
            [
                'b/.b/',
                'b/bb/',
            ],
            $this->asArray($path, $adapter->search($path, include: ['b/*/'], hidden: true)),
        );
    }

    public function testRead(): void {
        $path     = new FilePath(self::getTestData()->path('a/aa.txt'));
        $adapter  = new SymfonyFileSystem();
        $expected = "a\na\n";

        self::assertSame($expected, $adapter->read($path));
    }

    public function testWrite(): void {
        $file     = new TempFile();
        $adapter  = new SymfonyFileSystem();
        $expected = 'content';

        $adapter->write($file->path, $expected);

        self::assertSame($expected, file_get_contents($file->path->path));
    }

    public function testDelete(): void {
        // Prepare
        $adapter = new SymfonyFileSystem();
        $path    = new DirectoryPath(self::getTempDirectory());
        $file    = $path->file('file.txt');
        $dir     = $path->directory('dir/a/b/c');

        self::assertNotFalse(file_put_contents($file->path, 'content'));
        self::assertTrue(mkdir($dir->path, 0777, true));
        self::assertNotFalse(file_put_contents($dir->file('../file.txt')->path, 'content'));

        $adapter->delete($file);

        self::assertFalse(is_file($file->path));
        self::assertTrue(is_dir($dir->path));

        $adapter->delete($dir);

        self::assertFalse(is_dir($dir->path));
    }

    /**
     * @param iterable<mixed, FilePath|DirectoryPath> $iterable
     *
     * @return array<array-key, string>
     */
    private function asArray(DirectoryPath $root, iterable $iterable): array {
        $array = [];

        foreach ($iterable as $path) {
            $array[] = (string) $root->relative($path);
        }

        sort($array);

        return $array;
    }
}
