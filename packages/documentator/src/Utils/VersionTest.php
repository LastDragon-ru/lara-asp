<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Version::class)]
class VersionTest extends TestCase {
    public function testCompare(): void {
        self::assertEquals(0, Version::compare('1.2.3', '1.2.3'));
        self::assertEquals(0, Version::compare('v1.2.3', '1.2.3'));
        self::assertEquals(-1, Version::compare('1.0.0-beta.0', '1.0.0-beta.1'));
        self::assertEquals(1, Version::compare('1.2.3', '1.2.3-rc.0'));
        self::assertEquals(1, Version::compare('dev-main', '1.2.3'));
    }

    public function testIsVersion(): void {
        self::assertTrue(Version::isVersion('v1.2.3-beta.0'));
        self::assertTrue(Version::isVersion('1.2.3-beta.0'));
        self::assertTrue(Version::isVersion('1.2.3'));
        self::assertFalse(Version::isVersion('1.a.b'));
    }

    public function testIsSemver(): void {
        self::assertFalse(Version::isSemver('v1.2.3-beta.0'));
        self::assertTrue(Version::isSemver('1.2.3-beta.0'));
        self::assertTrue(Version::isSemver('1.2.3'));
        self::assertFalse(Version::isSemver('1.a.b'));
    }

    public function testNormalize(): void {
        self::assertEquals('1.2.3', Version::normalize('1.2.3'));
        self::assertEquals('1.2.3', Version::normalize('v1.2.3'));
        self::assertEquals('9999999-dev', Version::normalize('dev-main'));
    }
}
