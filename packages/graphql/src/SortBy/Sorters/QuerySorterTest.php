<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Closure;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderPropertyResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\QueryBuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

use function implode;
use function is_array;

/**
 * @internal
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
     * @param Closure(object, Property): string|null                            $resolver
     */
    public function testSort(
        array|Exception $expected,
        Closure $builder,
        Property $property,
        Direction $direction,
        ?Closure $resolver,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        if ($resolver) {
            $this->override(
                BuilderPropertyResolver::class,
                static function (MockInterface $mock) use ($resolver): void {
                    $mock
                        ->shouldReceive('getProperty')
                        ->once()
                        ->andReturnUsing($resolver);
                },
            );
        }

        $sorter  = Container::getInstance()->make(QuerySorter::class);
        $builder = $builder($this);
        $builder = $sorter->sort($builder, $property, $direction, null);

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
                'simple condition' => [
                    [
                        'query'    => 'select * from "test_objects" order by "a" asc',
                        'bindings' => [],
                    ],
                    new Property('a'),
                    Direction::Asc,
                    null,
                ],
                'property.path'    => [
                    [
                        'query'    => 'select * from "test_objects" order by "path"."to"."property" asc',
                        'bindings' => [],
                    ],
                    new Property('path', 'to', 'property'),
                    Direction::Asc,
                    null,
                ],
                'resolver'         => [
                    [
                        'query'    => 'select * from "test_objects" order by "path__to__property" asc',
                        'bindings' => [],
                    ],
                    new Property('path', 'to', 'property'),
                    Direction::Asc,
                    static function (object $builder, Property $property): string {
                        return implode('__', $property->getPath());
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
