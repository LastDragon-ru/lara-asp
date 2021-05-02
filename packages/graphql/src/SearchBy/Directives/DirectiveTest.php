<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use Closure;
use Exception;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\GreaterThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\GreaterThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\In;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNotNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\LessThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\LessThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Like;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotBetween;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotIn;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotLike;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AllOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\AnyOf;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\Not;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use ReflectionMethod;

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
     * @covers ::manipulateArgDefinition
     *
     * @dataProvider dataProviderManipulateArgDefinitionDirectiveArguments
     *
     * @param array<string> $expected
     */
    public function testManipulateArgDefinitionDirectiveArguments(
        array $expected,
        string $graphql,
        string $field,
    ): void {
        // We need to check the arguments of the directive, but the method is
        // protected -> this is a little hack to unprotect it.
        $method = new ReflectionMethod(Directive::class, 'directiveArgValue');

        $method->setAccessible(true);

        // Load schema and get Query
        $locator = $this->app->make(DirectiveLocator::class);
        $schema  = $this->getGraphQLSchema($this->getTestData()->file($graphql));
        $types   = $schema->getTypeMap();
        $query   = $types['Query'];

        $this->assertInstanceOf(ObjectType::class, $query);

        // Test
        /** @var \GraphQL\Type\Definition\ObjectType $query */
        $node       = $query->getField($field)->getArg('where')->astNode;
        $directives = $locator->associatedOfType($node, Directive::class);

        $this->assertCount(1, $directives);

        $this->assertEqualsCanonicalizing(
            $expected,
            $method->invoke($directives->first(), Directive::ArgOperators),
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

    /**
     * @return array<mixed>
     */
    public function dataProviderManipulateArgDefinitionDirectiveArguments(): array {
        return [
            'Properties' => [
                [
                    Between::class,
                    Equal::class,
                    GreaterThan::class,
                    GreaterThanOrEqual::class,
                    In::class,
                    IsNotNull::class,
                    IsNull::class,
                    LessThan::class,
                    LessThanOrEqual::class,
                    Like::class,
                    NotBetween::class,
                    NotEqual::class,
                    NotIn::class,
                    NotLike::class,
                    AllOf::class,
                    AnyOf::class,
                    Not::class,
                    Relation::class,
                ],
                '~full.graphql',
                'a',
            ],
            'Nested'     => [
                [
                    Equal::class,
                    NotEqual::class,
                    In::class,
                    NotIn::class,
                    IsNull::class,
                    IsNotNull::class,
                    Like::class,
                    NotLike::class,
                    AllOf::class,
                    AnyOf::class,
                    Not::class,
                    Relation::class,
                ],
                '~full.graphql',
                'b',
            ],
            'Property'   => [
                [
                    IsNull::class,
                    IsNotNull::class,
                    In::class,
                    NotIn::class,
                    Equal::class,
                    NotEqual::class,
                    AllOf::class,
                    AnyOf::class,
                    Not::class,
                ],
                '~full.graphql',
                'c',
            ],
        ];
    }
    // </editor-fold>
}
