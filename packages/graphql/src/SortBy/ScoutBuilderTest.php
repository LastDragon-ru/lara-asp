<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\ScoutColumnResolver;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client\SortClauseEmpty;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client\SortClauseTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

use function implode;
use function is_array;
use function json_decode;
use function json_encode;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\ScoutBuilder
 */
class ScoutBuilderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::build
     *
     * @dataProvider dataProviderBuild
     *
     * @param array<mixed> $clause
     */
    public function testBuild(array|Exception $expected, array $clause, Closure $resolver = null): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        if ($resolver) {
            $this->override(ScoutColumnResolver::class, $resolver);
        }

        $directive = $this->app->make(Directive::class);
        $builder   = $this->app->make(Builder::class, [
            'query' => '',
            'model' => new class() extends Model {
                // empty
            },
        ]);
        $builder   = $directive->handleScoutBuilder($builder, $clause);
        $actual    = json_decode(json_encode($builder), true);
        $default   = [
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
            $this->assertEquals($expected + $default, $actual + $default);
        }
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderBuild(): array {
        return [
            'empty clause'           => [
                new SortClauseEmpty(),
                [
                    [],
                ],
            ],
            'more than one property' => [
                new SortClauseTooManyProperties(['a', 'b']),
                [
                    [
                        'a' => 'asc',
                        'b' => 'asc',
                    ],
                ],
            ],
            'empty'                  => [
                [
                    // empty
                ],
                [],
            ],
            'clause'                 => [
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
                    ],
                ],
                [
                    [
                        'a' => 'asc',
                    ],
                    [
                        'b' => 'desc',
                    ],
                    [
                        'c' => [
                            'd' => ['e' => 'desc'],
                        ],
                    ],
                ],
            ],
            'clause with resolver'   => [
                [
                    'orders' => [
                        [
                            'column'    => 'properties/a/b',
                            'direction' => 'asc',
                        ],
                    ],
                ],
                [
                    [
                        'a' => [
                            'b' => 'asc',
                        ],
                    ],
                ],
                static function (): ScoutColumnResolver {
                    return new class() implements ScoutColumnResolver {
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
