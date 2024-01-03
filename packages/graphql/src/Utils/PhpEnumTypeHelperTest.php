<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use GraphQL\Type\Definition\PhpEnumType;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PhpEnumTypeHelper::class)]
class PhpEnumTypeHelperTest extends TestCase {
    public function testGetEnumClass(): void {
        $type     = new PhpEnumType(PhpEnumTypeHelperTest_Enum::class);
        $actual   = PhpEnumTypeHelper::getEnumClass($type);
        $expected = PhpEnumTypeHelperTest_Enum::class;

        self::assertEquals($expected, $actual);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum PhpEnumTypeHelperTest_Enum {
    case A;
}
