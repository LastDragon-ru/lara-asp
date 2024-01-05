<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Closure;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\QueryBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use PHPUnit\Framework\Attributes\CoversClass;

use function is_array;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(QuerySorter::class)]
class QuerySorterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSort
     *
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param Closure(static): QueryBuilder                                     $builder
     */
    public function testSort(
        array|Exception $expected,
        Closure $builder,
        Property $property,
        Direction $direction,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $builder = $builder($this);
        $builder = Container::getInstance()->make(QuerySorter::class)->sort($builder, $property, $direction);

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
     * @return array<array-key, mixed>
     */
    public static function dataProviderSort(): array {
        return (new CompositeDataProvider(
            new QueryBuilderDataProvider(),
            new ArrayDataProvider([
                'simple condition'     => [
                    [
                        'query'    => 'select * from "test_objects" order by "a" asc',
                        'bindings' => [],
                    ],
                    new Property('a'),
                    Direction::Asc,
                ],
                'nested not supported' => [
                    [
                        'query'    => 'select * from "test_objects" order by "test"."name" asc',
                        'bindings' => [],
                    ],
                    new Property('test', 'name'),
                    Direction::Asc,
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
