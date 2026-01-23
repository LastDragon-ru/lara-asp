<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use LastDragon_ru\PhpUnit\Utils\TempDirectory;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @deprecated %{VERSION} The {@see TempDirectory} should be used instead.
 * @internal
 * @see TempDirectory
 */
#[CoversClass(TestData::class)]
final class WithTempDirectoryTest extends TestCase {
    public function testGetTempDirectory(): void {
        $class     = new class () {
            use WithTempDirectory;
        };
        $directory = $class::getTempDirectory();

        self::assertFileExists($directory);
    }
}
