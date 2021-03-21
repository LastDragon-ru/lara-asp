<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\GraphQL\Testing\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\SortBuilder
 */
class SortBuilderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::build
     *
     * @dataProvider dataProviderBuild
     *
     * @param array<mixed> $clause
     */
    public function testBuild(array|Exception $expected, Closure $builder, array $clause): void {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $directive = $this->app->make(SortByDirective::class);
        $builder   = $builder($this);
        $builder   = $directive->handleBuilder($builder, $clause);
        $actual    = [
            'sql'      => $builder->toSql(),
            'bindings' => $builder->getBindings(),
        ];

        $this->assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderBuild(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'empty'                  => [
                    [
                        'sql'      => 'select * from "tmp"',
                        'bindings' => [],
                    ],
                    [],
                ],
                'empty clause'           => [
                    new SortLogicException(
                        'Sort clause cannot be empty.',
                    ),
                    [
                        [],
                    ],
                ],
                'more than one property' => [
                    new SortLogicException(
                        'Only one property allowed, found: `a`, `b`.',
                    ),
                    [
                        [
                            'a' => 'asc',
                            'b' => 'asc',
                        ],
                    ],
                ],
                'valid condition'        => [
                    [
                        'sql'      => 'select * from "tmp" order by "a" asc',
                        'bindings' => [],
                    ],
                    [
                        [
                            'a' => 'asc',
                        ],
                    ],
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
