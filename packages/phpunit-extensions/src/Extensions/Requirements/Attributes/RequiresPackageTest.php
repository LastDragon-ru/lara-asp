<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\Requirements\Attributes;

use LastDragon_ru\PhpUnit\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(RequiresPackage::class)]
final class RequiresPackageTest extends TestCase {
    public function testIsSatisfied(): void {
        self::assertTrue((new RequiresPackage('phpunit/phpunit'))->isSatisfied());
        self::assertTrue((new RequiresPackage('phpunit/phpunit', '>=10.0.0'))->isSatisfied());
        self::assertFalse((new RequiresPackage('phpunit/phpunit', '<10.0.0'))->isSatisfied());
    }

    public function testToString(): void {
        self::assertSame(
            'The package `phpunit/phpunit:>=10.0.0` is not installed.',
            (string) new RequiresPackage('phpunit/phpunit', '>=10.0.0'),
        );
    }
}
