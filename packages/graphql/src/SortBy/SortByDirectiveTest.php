<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\GraphQL\Testing\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\SortByDirective
 */
class SortByDirectiveTest extends TestCase {
    use WithTestData;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::manipulateArgDefinition
     *
     * @dataProvider dataProviderManipulateArgDefinition
     */
    public function testManipulateArgDefinition(string $expected, string $graphql): void {
        $expected = $this->getTestData()->content($expected);
        $actual   = $this->getSchema($this->getTestData()->content($graphql));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::handleBuilder
     *
     * @dataProvider dataProviderHandleBuilder
     *
     * @param array<mixed> $clause
     */
    public function testHandleBuilder(array|Exception $expected, Closure $builder, array $clause): void {
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
    public function dataProviderManipulateArgDefinition(): array {
        return [
            'full' => ['~full-expected.graphql', '~full.graphql'],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderHandleBuilder(): array {
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
