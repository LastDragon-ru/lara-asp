<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use Closure;
use Exception;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
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
use Nuwave\Lighthouse\Schema\TypeRegistry;
use ReflectionMethod;

use function sort;

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
     */
    public function testManipulateArgDefinitionDirectiveArguments(): void {
        // We need to check the arguments of the directive, but the method is
        // protected -> this is a little hack to unprotect it.
        $method = new ReflectionMethod(Directive::class, 'directiveArgValue');

        $method->setAccessible(true);

        // Load schema and get Query
        $locator = $this->app->make(DirectiveLocator::class);
        $schema  = $this->getGraphQLSchema($this->getTestData()->file('~full.graphql'));
        $types   = $schema->getTypeMap();
        $query   = $types['Query'];

        $this->assertInstanceOf(ObjectType::class, $query);

        // Collect
        $operators = [];

        foreach ($query->getFields() as $field) {
            $node       = $field->getArg('where')?->astNode;
            $directives = $locator->associatedOfType($node, Directive::class);

            $this->assertCount(1, $directives);

            $operators[$field->name] = $method->invoke($directives->first(), Directive::ArgOperators);

            sort($operators[$field->name]);
        }

        $this->assertEquals(
            [
                'a' => [
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
                    Relation::class,
                    AllOf::class,
                    AnyOf::class,
                    Not::class,
                ],
                'b' => [
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
                    Relation::class,
                    AllOf::class,
                    AnyOf::class,
                    Not::class,
                ],
                'c' => [
                    Equal::class,
                    In::class,
                    IsNotNull::class,
                    IsNull::class,
                    NotEqual::class,
                    NotIn::class,
                    AllOf::class,
                    AnyOf::class,
                    Not::class,
                ],
                'd' => [
                    Between::class,
                    Equal::class,
                    GreaterThan::class,
                    GreaterThanOrEqual::class,
                    In::class,
                    LessThan::class,
                    LessThanOrEqual::class,
                    NotBetween::class,
                    NotEqual::class,
                    NotIn::class,
                    Relation::class,
                    AllOf::class,
                    AnyOf::class,
                    Not::class,
                ],
            ],
            $operators,
        );
    }

    /**
     * @covers ::manipulateArgDefinition
     */
    public function testManipulateArgDefinitionUnknownType(): void {
        $this->expectExceptionObject(new TypeDefinitionUnknown('UnknownType'));

        $this->printGraphQLSchema($this->getTestData()->file('~unknown.graphql'));
    }

    /**
     * @covers ::manipulateArgDefinition
     */
    public function testManipulateArgDefinitionProgrammaticallyAddedType(): void {
        $this->app->make(TypeRegistry::class)->register(new EnumType([
            'name'   => 'EnumCreateProgrammatically',
            'values' => [
                'property' => [
                    'value'       => 123,
                    'description' => 'test property',
                ],
            ],
        ]));

        $this->assertGraphQLSchemaEquals(
            $this->getTestData()->file('~programmatically-expected.graphql'),
            $this->getTestData()->file('~programmatically.graphql'),
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
        $directive = new class($this->app) extends Directive {
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
