<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils\Enum;

use LastDragon_ru\LaraASP\Core\Enum;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\TypeRegistry;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Utils\Enum\Factory
 */
class FactoryTest extends TestCase {
    /**
     * @covers ::getType
     */
    public function testGetType(): void {
        $a        = Factory::getType(EnumHelperTest__A::class);
        $b        = Factory::getType(EnumHelperTest__B::class, 'B');
        $registry = $this->app->make(TypeRegistry::class);

        $registry->register($a);
        $registry->register($b);

        $expected = $this->getTestData()->file('.graphql');
        $actual   = /** @lang GraphQL */
            <<<'GRAPHQL'
            type Query {
              test(a: EnumHelperTest__A, b: B): ID! @all
            }
            GRAPHQL;

        self::assertGraphQLSchemaEquals($expected, $actual);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class EnumHelperTest__A extends Enum {
    public static function aaA(): static {
        return static::make(__FUNCTION__);
    }

    /**
     * Summary summary summary summary summary summary. Summary summary summary
     * summary summary summary. Summary summary summary summary summary
     * summary. Summary summary summary summary summary summary.
     *
     * Description description description description description. Description
     * description description description description Description description
     * description description description.
     *
     * Description description description description description. Description
     * description description description description Description description
     * description description description.
     */
    public static function bbB(): static {
        return static::make(__FUNCTION__);
    }
}

/**
 * Summary summary summary summary summary summary. Summary summary summary
 * summary summary summary. Summary summary summary summary summary
 * summary. Summary summary summary summary summary summary.
 *
 * Description description description description description. Description
 * description description description description Description description
 * description description description.
 *
 * Description description description description description. Description
 * description description description description Description description
 * description description description.
 *
 * @internal
 *
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class EnumHelperTest__B extends Enum {
    public static function a(): static {
        return static::make(__FUNCTION__);
    }

    public static function b(): static {
        return static::make(__FUNCTION__);
    }
}
