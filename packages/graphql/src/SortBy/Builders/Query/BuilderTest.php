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

use function is_array;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent\Builder
 *
 * @phpstan-import-type BuilderFactory from \LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider
 */
class BuilderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::handle
     *
     * @dataProvider dataProviderHandle
     *
     * @param array{query: string, bindings: array<mixed>}|Exception $expected
     * @param BuilderFactory                                         $builder
     * @param array<Clause>                                          $clauses
     */
    public function testHandle(array|Exception $expected, Closure $builder, array $clauses): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $builder = $builder($this);
        $builder = $this->app->make(Builder::class)->handle($builder, $clauses);

        if (is_array($expected)) {
            self::assertDatabaseQueryEquals($expected, $builder);
        } else {
            self::fail('Something wrong...');
        }
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
                        'query'    => 'select * from "tmp"',
                        'bindings' => [],
                    ],
                    [],
                ],
                'simple condition'     => [
                    [
                        'query'    => 'select * from "tmp" order by "a" asc',
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
