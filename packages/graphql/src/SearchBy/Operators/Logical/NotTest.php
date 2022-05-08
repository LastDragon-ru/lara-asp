<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\Not
 */
class NotTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::apply
     *
     * @dataProvider dataProviderApply
     *
     * @param array{query: string, bindings: array<mixed>} $expected
     * @param Closure(static): object                      $builderFactory
     * @param Closure(static): Argument                    $argumentFactory
     */
    public function testApply(
        array $expected,
        Closure $builderFactory,
        Property $property,
        Closure $argumentFactory,
    ): void {
        $operator = $this->app->make(Not::class);
        $argument = $argumentFactory($this);
        $search   = $this->app->make(Directive::class);
        $builder  = $builderFactory($this);
        $builder  = $operator->call($search, $builder, $property, $argument);

        self::assertDatabaseQueryEquals($expected, $builder);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderApply(): array {
        $factory = static function (self $test): Argument {
            return $test->getGraphQLArgument(
                'TestInput',
                [
                    'a' => [
                        'equal' => 2,
                    ],
                ],
                <<<'GRAPHQL'
                    input TestInput {
                        a: TestOperators

                        b: TestOperators
                    }

                    input TestOperators {
                        equal: Int
                        @searchByOperatorEqual

                        notEqual: Int
                        @searchByOperatorNotEqual
                    }

                    type Query {
                        test(input: TestInput): Int @all
                    }
                GRAPHQL,
            );
        };

        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'property'   => [
                    [
                        'query'    => 'select * from "tmp" where not ("a" = ?)',
                        'bindings' => [
                            2,
                        ],
                    ],
                    new Property(),
                    $factory,
                ],
                'with alias' => [
                    [
                        'query'    => 'select * from "tmp" where not ("alias"."a" = ?)',
                        'bindings' => [
                            2,
                        ],
                    ],
                    new Property('alias'),
                    $factory,
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
