<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Filesystem;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\PhpUnit\Filesystem\Constraints\DirectoryEquals;
use PHPUnit\Framework\Assert;

use function is_string;

/**
 * @mixin Assert
 */
trait Assertions {
    /**
     * Asserts that Directory equals Directory.
     *
     * @see DirectoryEquals
     */
    public static function assertDirectoryEquals(
        DirectoryPath|string $expected,
        DirectoryPath|string $actual,
        string $message = '',
    ): void {
        $expected = is_string($expected) ? new DirectoryPath($expected) : $expected;
        $actual   = is_string($actual) ? new DirectoryPath($actual) : $actual;

        static::assertDirectoryExists($expected->path);
        static::assertDirectoryExists($actual->path);
        static::assertThat($actual, new DirectoryEquals($expected), $message);
    }
}
