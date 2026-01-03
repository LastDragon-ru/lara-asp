<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Cache::class)]
final class CacheTest extends TestCase {
    public function testArrayAccess(): void {
        $cache = new Cache(1);
        $aPath = new FilePath('file.txt');
        $aFile = Mockery::mock(File::class);

        self::assertFalse(isset($cache[$aPath]));

        $cache[$aPath] = $aFile;

        self::assertTrue(isset($cache[$aPath]));
        self::assertSame($aFile, $cache[$aPath] ?? null);
        self::assertTrue(isset($cache[new FilePath('file.txt')]));
        self::assertFalse(isset($cache[new FilePath('another.txt')]));

        unset($cache[new FilePath('file.txt')]);

        self::assertFalse(isset($cache[$aPath]));
    }

    public function testCleanup(): void {
        $fs    = Mockery::mock(FileSystem::class);
        $cache = new Cache(1);
        $aPath = new FilePath('/a.txt');
        $aFile = new File($fs, $aPath);
        $bPath = new FilePath('/b.txt');
        $bFile = new File($fs, $bPath);
        $cPath = new FilePath('/c.txt');

        $cache[$aPath] = $aFile;
        $cache[$bPath] = $bFile;
        $cache[$cPath] = new File($fs, $cPath);

        $cache->cleanup();

        self::assertTrue(isset($cache[$aPath]));
        self::assertTrue(isset($cache[$bPath]));
        self::assertTrue(isset($cache[$cPath]));

        self::assertSame($bFile, $cache[$bPath]);

        $cache->cleanup();
        $cache->cleanup();

        self::assertTrue(isset($cache[$aPath]));
        self::assertTrue(isset($cache[$bPath]));
        self::assertFalse(isset($cache[$cPath]));

        unset($aFile);

        $cache->cleanup();

        self::assertFalse(isset($cache[$aPath]));
        self::assertTrue(isset($cache[$bPath]));
    }
}
