<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Composer;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
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
                'a/b/c/Class.php',
                'a/b/C/Class.php',
            ],
            $package->resolve('\\A\\B\\C\\Class'),
        );
        self::assertEquals(
            [
                'a/b/Class.php',
            ],
            $package->resolve('\\A\\B\\Class'),
        );
        self::assertEquals(
            [
                'c/a/Class.php',
                'c/b/Class.php',
                'Class.php',
            ],
            $package->resolve('\\C\\Class'),
        );
        self::assertEquals(
            [
                'c/a/D/Class.php',
                'c/b/D/Class.php',
                'D/Class.php',
            ],
            $package->resolve('\\C\\D\\Class'),
        );
        self::assertNull(
            $package->resolve('\\Class'),
        );
    }
}
