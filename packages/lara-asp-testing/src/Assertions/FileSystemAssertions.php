<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Constraints\FileSystem\DirectoryMatchesDirectory;
use LastDragon_ru\PhpUnit\Filesystem\Assertions;
use PHPUnit\Framework\Assert;

/**
 * @deprecated %{VERSION} The {@see Assertions} should be used instead.
 * @mixin Assert
 * @see Assertions
 */
trait FileSystemAssertions {
    /**
     * Asserts that Directory equals Directory.
     */
    public static function assertDirectoryEquals(string $expected, string $actual, string $message = ''): void {
        static::assertThat($actual, new DirectoryMatchesDirectory($expected), $message);
    }
}
