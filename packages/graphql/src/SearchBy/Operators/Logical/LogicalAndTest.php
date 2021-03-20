<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Testing\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\LogicalAnd
 */
class LogicalAndTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::apply
     *
     * @dataProvider dataProviderApply
     *
     * @param array{sql: string, bindings: array<mixed>} $expected
     */
    public function testApply(array $expected, Closure $builder, Closure $nested): void {
        $operator = $this->app->make(LogicalAnd::class);
        $builder  = $builder($this);
        $builder  = $operator->apply($builder, $nested);
        $builder  = $operator->apply($builder, $nested);
        $actual   = [
            'sql'      => $builder->toSql(),
            'bindings' => $builder->getBindings(),
        ];

        $this->assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderApply(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'and' => [
                    [
                        'sql'      => 'select * from "tmp" where (1 = 1) and (1 = 1)',
                        'bindings' => [],
                    ],
                    static function (EloquentBuilder|QueryBuilder $builder): EloquentBuilder|QueryBuilder {
                        return $builder->whereRaw('1 = 1');
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
