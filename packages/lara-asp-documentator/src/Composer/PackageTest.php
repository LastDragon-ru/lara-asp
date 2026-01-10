<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Composer;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Package::class)]
final class PackageTest extends TestCase {
    public function testResolve(): void {
        $package = new Package(
            new ComposerJson(
                autoload   : new Autoload([
                    '\\A\\B\\C\\' => ['a/b/c/'],
                    '\\A\\B\\'    => 'a/b/',
                ]),
                autoloadDev: new Autoload([
                    '\\C\\' => ['c/a', 'c/b', ''],
                ]),
            ),
        );

        self::assertEquals(
            [
                (new FilePath('a/b/c/Class.php'))->normalized(),
                (new FilePath('a/b/C/Class.php'))->normalized(),
            ],
            $package->resolve('\\A\\B\\C\\Class'),
        );
        self::assertEquals(
            [
                (new FilePath('a/b/Class.php'))->normalized(),
            ],
            $package->resolve('\\A\\B\\Class'),
        );
        self::assertEquals(
            [
                (new FilePath('c/a/Class.php'))->normalized(),
                (new FilePath('c/b/Class.php'))->normalized(),
                (new FilePath('Class.php'))->normalized(),
            ],
            $package->resolve('\\C\\Class'),
        );
        self::assertEquals(
            [
                (new FilePath('c/a/D/Class.php'))->normalized(),
                (new FilePath('c/b/D/Class.php'))->normalized(),
                (new FilePath('D/Class.php'))->normalized(),
            ],
            $package->resolve('\\C\\D\\Class'),
        );
        self::assertNull(
            $package->resolve('\\Class'),
        );
    }
}
