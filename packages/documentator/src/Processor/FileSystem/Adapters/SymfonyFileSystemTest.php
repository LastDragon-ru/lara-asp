<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

use function file_get_contents;
use function sort;

/**
 * @internal
 */
#[CoversClass(SymfonyFileSystem::class)]
final class SymfonyFileSystemTest extends TestCase {
    public function testIsFile(): void {
        $adapter = new SymfonyFileSystem();

        self::assertTrue($adapter->isFile(new FilePath(__FILE__)));
        self::assertFalse($adapter->isFile(new FilePath(__DIR__)));
    }

    public function testIsDirectory(): void {
        $adapter = new SymfonyFileSystem();

        self::assertFalse($adapter->isDirectory(new DirectoryPath(__FILE__)));
        self::assertTrue($adapter->isDirectory(new DirectoryPath(__DIR__)));
    }

    public function testGetFilesIterator(): void {
        $path    = new DirectoryPath(self::getTestData()->path(''));
        $adapter = new SymfonyFileSystem();

        self::assertSame(
            [
                'a.txt',
                'a/aa.txt',
                'a/aa/aaa.txt',
                'b.txt',
                'b/bb.txt',
                'b/bb/bbb.txt',
            ],
            $this->asArray($path, $adapter->getFilesIterator($path)),
        );
        self::assertSame(
            [
                'a.txt',
                'b.txt',
            ],
            $this->asArray($path, $adapter->getFilesIterator($path, depth: 0)),
        );
        self::assertSame(
            [
                'a.txt',
                'b.txt',
                'b/bb.txt',
                'b/bb/bbb.txt',
            ],
            $this->asArray($path, $adapter->getFilesIterator($path, exclude: ['a/**/*.txt'])),
        );
        self::assertSame(
            [
                'a.txt',
            ],
            $this->asArray($path, $adapter->getFilesIterator($path, include: ['a.txt'])),
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
            $this->asArray($path, $adapter->getFilesIterator($path, include: ['**/*.txt'])),
        );
        self::assertSame(
            [
                'a.txt',
                'a/aa.txt',
                'b.txt',
                'b/bb.txt',
                'b/bb/bbb.txt',
            ],
            $this->asArray($path, $adapter->getFilesIterator($path, include: ['**/*.txt'], exclude: ['**/aa/*'])),
        );
    }

    public function testRead(): void {
        $path     = new FilePath(self::getTestData()->path('a/aa.txt'));
        $adapter  = new SymfonyFileSystem();
        $expected = "a\na\n";

        self::assertSame($expected, $adapter->read($path));
    }

    public function testWrite(): void {
        $path     = self::getTempFile()->getPathname();
        $adapter  = new SymfonyFileSystem();
        $expected = 'content';

        self::assertNotEmpty($path);

        $adapter->write(new FilePath($path), $expected);

        self::assertSame($expected, file_get_contents($path));
    }

    /**
     * @param iterable<array-key, FilePath|DirectoryPath> $iterable
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
