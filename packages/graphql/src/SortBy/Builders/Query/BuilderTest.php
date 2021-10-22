<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query;

use Closure;
use Exception;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\BuilderUnsupported;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\QueryBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent\Builder
 */
class BuilderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::handle
     *
     * @dataProvider dataProviderHandle
     *
     * @param array<mixed>|Exception $expected
     * @param array<Clause>          $clauses
     */
    public function testHandle(array|Exception $expected, Closure $builder, array $clauses): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $builder = $builder($this);
        $builder = $this->app->make(Builder::class)->handle($builder, $clauses);
        $actual  = $this->getSql($builder);

        $this->assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderHandle(): array {
        return (new CompositeDataProvider(
            new QueryBuilderDataProvider(),
            new ArrayDataProvider([
                'empty'                => [
                    [
                        'sql'      => 'select * from "tmp"',
                        'bindings' => [],
                    ],
                    [],
                ],
                'simple condition'     => [
                    [
                        'sql'      => 'select * from "tmp" order by "a" asc',
                        'bindings' => [],
                    ],
                    [
                        new Clause(['a'], 'asc'),
                    ],
                ],
                'nested not supported' => [
                    new BuilderUnsupported(QueryBuilder::class),
                    [
                        new Clause(['test', 'name'], 'asc'),
                    ],
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
