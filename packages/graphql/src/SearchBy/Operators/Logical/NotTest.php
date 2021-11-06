<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\EloquentBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\QueryBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\MergeDataProvider;

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
     * @param array<mixed>                                 $conditions
     */
    public function testApply(array $expected, Closure $builder, array $conditions, ?string $tableAlias): void {
        $search   = new SearchBuilder([
            $this->app->make(AllOf::class),
            $this->app->make(Equal::class),
            $this->app->make(NotEqual::class),
        ]);
        $operator = $this->app->make(Not::class);
        $builder  = $builder($this);
        $builder  = $operator->apply($search, $builder, $conditions, $tableAlias);

        self::assertDatabaseQueryEquals($expected, $builder);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderApply(): array {
        return (new MergeDataProvider([
            'Both'     => (new CompositeDataProvider(
                new BuilderDataProvider(),
                new ArrayDataProvider([
                    'not with alias' => [
                        [
                            'query'    => 'select * from "tmp" where not ((("alias"."a" = ?) and ("alias"."b" != ?)))',
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        [
                            'allOf' => [
                                ['a' => ['equal' => 2]],
                                ['b' => ['notEqual' => 22]],
                            ],
                        ],
                        'alias',
                    ],
                ]),
            )),
            'Query'    => (new CompositeDataProvider(
                new QueryBuilderDataProvider(),
                new ArrayDataProvider([
                    'not' => [
                        [
                            'query'    => 'select * from "tmp" where not ((("a" = ?) and ("b" != ?)))',
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        [
                            'allOf' => [
                                ['a' => ['equal' => 2]],
                                ['b' => ['notEqual' => 22]],
                            ],
                        ],
                        null,
                    ],
                ]),
            )),
            'Eloquent' => (new CompositeDataProvider(
                new EloquentBuilderDataProvider(),
                new ArrayDataProvider([
                    'not' => [
                        [
                            'query'    => 'select * from "tmp" where not ((("tmp"."a" = ?) and ("tmp"."b" != ?)))',
                            'bindings' => [
                                2,
                                22,
                            ],
                        ],
                        [
                            'allOf' => [
                                ['a' => ['equal' => 2]],
                                ['b' => ['notEqual' => 22]],
                            ],
                        ],
                        null,
                    ],
                ]),
            )),
        ]))->getData();
    }
    // </editor-fold>
}
