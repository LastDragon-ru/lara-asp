<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils\Enum;

use LastDragon_ru\LaraASP\Core\Enum;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\Utils\Enum\Factory
 */
class FactoryTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::getDefinition
     *
     * @dataProvider dataProviderGetDefinition
     *
     * @param array<string,mixed> $expected
     * @param class-string<Enum>  $enum
     */
    public function testGetDefinition(array $expected, string $enum): void {
        self::assertEquals($expected, Factory::getDefinition($enum));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{array<string,mixed>,class-string<Enum>}>
     */
    public function dataProviderGetDefinition(): array {
        return [
            FactoryTest__A::class => [
                [
                    'name'        => 'FactoryTest__A',
                    'description' => null,
                    'values'      => [
                        'AaA' => [
                            'value'       => FactoryTest__A::aaA(),
                            'description' => null,
                        ],
                        'BbB' => [
                            'value'       => FactoryTest__A::bbB(),
                            'description' => <<<'STRING'
                                Summary summary summary summary summary summary. Summary summary summary
                                summary summary summary. Summary summary summary summary summary
                                summary. Summary summary summary summary summary summary.

                                Description description description description description. Description
                                description description description description Description description
                                description description description.

                                Description description description description description. Description
                                description description description description Description description
                                description description description.
                                STRING
                            ,
                        ],
                    ],
                ],
                FactoryTest__A::class,
            ],
            FactoryTest__B::class => [
                [
                    'name'        => 'FactoryTest__B',
                    'description' => <<<'STRING'
                        Summary summary summary summary summary summary. Summary summary summary
                        summary summary summary. Summary summary summary summary summary
                        summary. Summary summary summary summary summary summary.

                        Description description description description description. Description
                        description description description description Description description
                        description description description.

                        Description description description description description. Description
                        description description description description Description description
                        description description description.
                        STRING
                    ,
                    'values'      => [
                        'A' => [
                            'value'       => FactoryTest__B::a(),
                            'description' => null,
                        ],
                        'B' => [
                            'value'       => FactoryTest__B::b(),
                            'description' => null,
                        ],
                    ],
                ],
                FactoryTest__B::class,
            ],
        ];
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class FactoryTest__A extends Enum {
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
class FactoryTest__B extends Enum {
    public static function a(): static {
        return static::make(__FUNCTION__);
    }

    public static function b(): static {
        return static::make(__FUNCTION__);
    }
}
