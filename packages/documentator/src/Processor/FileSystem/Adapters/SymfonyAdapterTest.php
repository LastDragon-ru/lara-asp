<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function file_get_contents;
use function sort;

/**
 * @internal
 */
#[CoversClass(SymfonyAdapter::class)]
final class SymfonyAdapterTest extends TestCase {
    public function testIsFile(): void {
        $adapter = new SymfonyAdapter();

        self::assertTrue($adapter->isFile(__FILE__));
        self::assertFalse($adapter->isFile(__DIR__));
    }

    public function testIsDirectory(): void {
        $adapter = new SymfonyAdapter();

        self::assertFalse($adapter->isDirectory(__FILE__));
        self::assertTrue($adapter->isDirectory(__DIR__));
    }

    public function testGetFilesIterator(): void {
        $path    = self::getTestData()->path('');
        $adapter = new SymfonyAdapter();

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
            $this->asArray($path, $adapter->getFilesIterator($path, exclude: 'a/**/*.txt')),
        );
        self::assertSame(
            [
                'a.txt',
            ],
            $this->asArray($path, $adapter->getFilesIterator($path, include: 'a.txt')),
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
            $this->asArray($path, $adapter->getFilesIterator($path, include: '**/*.txt')),
        );
    }

    public function testGetDirectoriesIterator(): void {
        $path    = self::getTestData()->path('');
        $adapter = new SymfonyAdapter();

        self::assertSame(
            [
                'a',
                'a/aa',
                'b',
                'b/bb',
            ],
            $this->asArray($path, $adapter->getDirectoriesIterator($path)),
        );
        self::assertSame(
            [
                'a',
                'b',
            ],
            $this->asArray($path, $adapter->getDirectoriesIterator($path, depth: 0)),
        );
        self::assertSame(
            [
                'a',
                'b',
                'b/bb',
            ],
            $this->asArray($path, $adapter->getDirectoriesIterator($path, exclude: 'a/*')),
        );
        self::assertSame(
            [
                'a',
            ],
            $this->asArray($path, $adapter->getDirectoriesIterator($path, include: 'a')),
        );
    }

    public function testRead(): void {
        $path     = self::getTestData()->path('a/aa.txt');
        $adapter  = new SymfonyAdapter();
        $expected = "a\na\n";

        self::assertSame($expected, $adapter->read($path));
    }

    public function testWrite(): void {
        $path     = self::getTempFile()->getPathname();
        $adapter  = new SymfonyAdapter();
        $expected = 'content';

        $adapter->write($path, $expected);

        self::assertSame($expected, file_get_contents($path));
    }

    /**
     * @param iterable<array-key, string> $iterable
     *
     * @return array<array-key, string>
     */
    private function asArray(string $root, iterable $iterable): array {
        $root  = new DirectoryPath($root);
        $array = [];

        foreach ($iterable as $path) {
            $array[] = (string) $root->getRelativePath(new FilePath($path));
        }

        sort($array);

        return $array;
    }
}
