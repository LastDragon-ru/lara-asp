<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @deprecated %{VERSION} The `\LastDragon_ru\PhpUnit\Utils\TempFile` should be used instead.
 * @internal
 */
#[CoversClass(WithTempFile::class)]
final class WithTempFileTest extends TestCase {
    public function testGetTempFile(): void {
        $class = new class () {
            use WithTempFile;
        };
        $file  = $class::getTempFile();

        self::assertFileExists($file->getPathname());
    }
}
