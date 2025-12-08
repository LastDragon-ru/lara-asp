<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
#[CoversClass(SymfonyPathMap::class)]
final class SymfonyPathMapTest extends TestCase {
    public function testGet(): void {
        $file = new SplFileInfo(__FILE__, 'relative/path/to', 'relative/path/to/file.php');
        $dir  = new SplFileInfo(__DIR__, 'relative/path/to', 'relative/path/to/directory');
        $map  = new SymfonyPathMap();

        self::assertEquals((new FilePath('relative/path/to/file.php'))->normalized(), $map->get($file));
        self::assertSame($map->get($file), $map->get($file));

        self::assertEquals((new DirectoryPath('relative/path/to/directory'))->normalized(), $map->get($dir));
        self::assertSame($map->get($dir), $map->get($dir));
    }
}
