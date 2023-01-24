<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core;

use PHPUnit\Framework\TestCase;

use function json_encode;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Core\Enum
 */
class EnumTest extends TestCase {
    public function testToString(): void {
        self::assertEquals('1', (string) EnumTest_A::i());
        self::assertEquals('a', (string) EnumTest_A::a());
    }

    public function testJsonSerialize(): void {
        self::assertEquals(json_encode(1), json_encode(EnumTest_A::i()));
        self::assertEquals(json_encode('a'), json_encode(EnumTest_A::a()));
    }

    public function testGetValues(): void {
        self::assertEquals([
            'a' => EnumTest_A::a(),
            'b' => EnumTest_A::b(),
            1   => EnumTest_A::i(),
        ], EnumTest_A::getValues());
    }

    public function testGetValue(): void {
        self::assertEquals(1, EnumTest_A::i()->getValue());
        self::assertEquals('a', EnumTest_A::a()->getValue());
    }

    public function testGet(): void {
        self::assertSame(EnumTest_A::a(), EnumTest_A::get('a'));
        self::assertSame(EnumTest_A::i(), EnumTest_A::get(1));
        self::assertNotSame(EnumTest_A::a(), EnumTest_B::a());
        self::assertEquals(EnumTest_A::a()->getValue(), EnumTest_B::a()->getValue());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class EnumTest_A extends Enum {
    public static function a(): static {
        return self::make(__FUNCTION__);
    }

    public static function b(): static {
        return self::make(__FUNCTION__);
    }

    public static function i(): static {
        return self::make(1);
    }

    public function ignoredBecauseNonStatic(): void {
        // empty
    }

    public static function ignoredBecauseHasArgs(int $arg): void {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class EnumTest_B extends Enum {
    public static function a(): static {
        return self::make(__FUNCTION__);
    }
}
