<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Filesystem\Constraints;

use Exception;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\PhpUnit\Package;
use LastDragon_ru\PhpUnit\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\ExpectationFailedException;

use function bin2hex;
use function mkdir;
use function random_bytes;
use function rmdir;
use function sys_get_temp_dir;

/**
 * @internal
 */
#[CoversClass(DirectoryEmpty::class)]
final class DirectoryEmptyTest extends TestCase {
    public function testEvaluateEmpty(): void {
        $path      = sys_get_temp_dir().'/'.Package::Name.'-'.bin2hex(random_bytes(16));
        $directory = new DirectoryPath($path);

        self::assertDirectoryDoesNotExist($path);
        self::assertTrue(mkdir($path, 0700, true));
        self::assertTrue((new DirectoryEmpty())->evaluate($directory, '', true));
        self::assertTrue(rmdir($path));
    }

    public function testEvaluateFail(): void {
        $directory = new DirectoryPath(__DIR__);
        $exception = null;

        try {
            (new DirectoryEmpty())->evaluate($directory);
        } catch (Exception $exception) {
            // empty
        }

        self::assertInstanceOf(ExpectationFailedException::class, $exception);
        self::assertSame(
            "Failed asserting that directory '{$directory->path}' empty.",
            $exception->getMessage(),
        );
    }
}
