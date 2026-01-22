<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use LastDragon_ru\PhpUnit\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(TempDirectory::class)]
final class TempDirectoryTest extends TestCase {
    public function testWithoutParams(): void {
        $directory = new TempDirectory();
        $path      = $directory->path->path;

        self::assertDirectoryEmpty($path);

        unset($directory);

        self::assertDirectoryDoesNotExist($path);
    }

    public function testWithDirectoryPath(): void {
        $source    = TestData::get()->directory();
        $directory = new TempDirectory($source);
        $path      = $directory->path->path;

        self::assertDirectoryEquals($source, $directory->path);

        unset($directory);

        self::assertDirectoryDoesNotExist($path);
    }
}
