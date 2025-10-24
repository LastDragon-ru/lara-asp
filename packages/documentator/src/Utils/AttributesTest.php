<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Attribute;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Attributes::class)]
final class AttributesTest extends TestCase {
    public function testGet(): void {
        self::assertEquals(
            [
                // empty
            ],
            iterator_to_array(
                Attributes::get(AttributesTest__A::class, stdClass::class),
            ),
        );
        self::assertEquals(
            [
                new AttributesTest__Attribute('A1'),
                new AttributesTest__Attribute('A2'),
            ],
            iterator_to_array(
                Attributes::get(AttributesTest__A::class, AttributesTest__Attribute::class),
                false,
            ),
        );
        self::assertEquals(
            [
                new AttributesTest__Attribute('A1'),
                new AttributesTest__Attribute('A2'),
                new AttributesTest__Attribute('B1'),
            ],
            iterator_to_array(
                Attributes::get(AttributesTest__B::class, AttributesTest__Attribute::class),
                false,
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
readonly class AttributesTest__Attribute {
    public function __construct(
        public string $name,
    ) {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[AttributesTest__Attribute('A1')]
#[AttributesTest__Attribute('A2')]
class AttributesTest__A {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[AttributesTest__Attribute('B1')]
class AttributesTest__B extends AttributesTest__A {
    // empty
}
