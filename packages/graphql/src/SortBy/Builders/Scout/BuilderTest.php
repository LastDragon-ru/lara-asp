<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Clause;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

use function implode;
use function is_array;
use function json_decode;
use function json_encode;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout\Builder
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
    public function testHandle(array|Exception $expected, array $clauses, Closure $resolver = null): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        if ($resolver) {
            $this->override(ColumnResolver::class, $resolver);
        }

        $builder = $this->app->make(ScoutBuilder::class, [
            'query' => '',
            'model' => new class() extends Model {
                // empty
            },
        ]);
        $builder = $this->app->make(Builder::class)->handle($builder, $clauses);
        $actual  = json_decode((string) json_encode($builder), true);
        $default = [
            'model'         => [],
            'query'         => '',
            'callback'      => null,
            'queryCallback' => null,
            'index'         => null,
            'wheres'        => [],
            'whereIns'      => [],
            'limit'         => null,
            'orders'        => [],
        ];

        if (is_array($expected)) {
            self::assertEquals($expected + $default, $actual + $default);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderHandle(): array {
        return [
            'empty'                => [
                [
                    // empty
                ],
                [],
            ],
            'clause'               => [
                [
                    'orders' => [
                        [
                            'column'    => 'a',
                            'direction' => 'asc',
                        ],
                        [
                            'column'    => 'b',
                            'direction' => 'desc',
                        ],
                        [
                            'column'    => 'c.d.e',
                            'direction' => 'desc',
                        ],
                        [
                            'column'    => 'null',
                            'direction' => 'asc',
                        ],
                    ],
                ],
                [
                    new Clause(['a'], 'asc'),
                    new Clause(['b'], 'desc'),
                    new Clause(['c', 'd', 'e'], 'desc'),
                    new Clause(['null'], null),
                ],
            ],
            'clause with resolver' => [
                [
                    'orders' => [
                        [
                            'column'    => 'properties/a/b',
                            'direction' => 'asc',
                        ],
                    ],
                ],
                [
                    new Clause(['a', 'b'], 'asc'),
                ],
                static function (): ColumnResolver {
                    return new class() implements ColumnResolver {
                        /**
                         * @inheritDoc
                         */
                        public function getColumn(Model $model, array $path): string {
                            return 'properties/'.implode('/', $path);
                        }
                    };
                },
            ],
        ];
    }
    // </editor-fold>
}
