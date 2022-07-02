<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
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
     */
    public function testHandle(
        array|Exception $expected,
        Closure $builder,
        Property $property,
        string $direction,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $builder = $builder($this);
        $builder = $this->app->make(Builder::class)->handle($builder, $property, $direction);

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
                'simple condition'     => [
                    [
                        'query'    => 'select * from "tmp" order by "a" asc',
                        'bindings' => [],
                    ],
                    new Property('a'),
                    'asc',
                ],
                'nested not supported' => [
                    [
                        'query'    => 'select * from "tmp" order by "test"."name" asc',
                        'bindings' => [],
                    ],
                    new Property('test', 'name'),
                    'asc',
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
