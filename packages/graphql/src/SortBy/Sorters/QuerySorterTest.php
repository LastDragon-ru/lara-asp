<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use Closure;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
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
final class QuerySorterTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSort
     *
     * @param array{query: string, bindings: array<array-key, mixed>}|Exception $expected
     * @param Closure(static): QueryBuilder                                     $builder
     * @param Closure(object, Field): string|null                               $resolver
     */
    public function testSort(
        array|Exception $expected,
        Closure $builder,
        Field $field,
        Direction $direction,
        ?Closure $resolver,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        if ($resolver) {
            $this->override(
                BuilderFieldResolver::class,
                static function (MockInterface $mock) use ($resolver): void {
                    $mock
                        ->shouldReceive('getField')
                        ->once()
                        ->andReturnUsing($resolver);
                },
            );
        }

        $sorter  = Container::getInstance()->make(QuerySorter::class);
        $builder = $builder($this);
        $builder = $sorter->sort($builder, $field, $direction, null);

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
                    new Field('a'),
                    Direction::Asc,
                    null,
                ],
                'field.path'       => [
                    [
                        'query'    => 'select * from "test_objects" order by "path"."to"."field" asc',
                        'bindings' => [],
                    ],
                    new Field('path', 'to', 'field'),
                    Direction::Asc,
                    null,
                ],
                'resolver'         => [
                    [
                        'query'    => 'select * from "test_objects" order by "path__to__field" asc',
                        'bindings' => [],
                    ],
                    new Field('path', 'to', 'field'),
                    Direction::Asc,
                    static function (object $builder, Field $field): string {
                        return implode('__', $field->getPath());
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
