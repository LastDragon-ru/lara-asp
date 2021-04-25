<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AllOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AnyOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\Not;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive
 */
class DirectiveTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::manipulateArgDefinition
     *
     * @dataProvider dataProviderManipulateArgDefinition
     */
    public function testManipulateArgDefinition(string $expected, string $graphql): void {
        $this->assertGraphQLSchemaEquals(
            $this->getTestData()->file($expected),
            $this->getTestData()->file($graphql),
        );
    }

    /**
     * @covers ::handleBuilder
     *
     * @dataProvider dataProviderHandleBuilder
     *
     * @param array<mixed> $input
     */
    public function testHandleBuilder(bool|Exception $expected, Closure $builder, array $input): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $builder   = $builder($this);
        $directive = new class(
            $this->app,
            $this->app->make(PackageTranslator::class),
        ) extends Directive {
            /**
             * @inheritDoc
             */
            protected function directiveArgValue(string $name, $default = null): mixed {
                return $name !== Directive::ArgOperators
                    ? parent::directiveArgValue($name, $default)
                    : [
                        Not::class,
                        AllOf::class,
                        AnyOf::class,
                        Equal::class,
                        NotEqual::class,
                    ];
            }
        };

        $this->assertNotNull($directive->handleBuilder($builder, $input));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderManipulateArgDefinition(): array {
        return [
            'full'                           => ['~full-expected.graphql', '~full.graphql'],
            'only used type should be added' => ['~usedonly-expected.graphql', '~usedonly.graphql'],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderHandleBuilder(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'valid condition' => [
                    true,
                    [
                        'not' => [
                            'allOf' => [
                                [
                                    'a' => [
                                        'notEqual' => 1,
                                    ],
                                ],
                                [
                                    'anyOf' => [
                                        [
                                            'a' => [
                                                'equal' => 2,
                                            ],
                                        ],
                                        [
                                            'b' => [
                                                'notEqual' => 3,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
