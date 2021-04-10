<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\In
 */
class InTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::apply
     *
     * @dataProvider dataProviderApply
     *
     * @param array{sql: string, bindings: array<mixed>} $expected
     */
    public function testApply(
        array $expected,
        Closure $builder,
        string $property,
        mixed $value,
    ): void {
        $operator = $this->app->make(In::class);
        $builder  = $builder($this);
        $builder  = $operator->apply($builder, $property, $value);
        $actual   = [
            'sql'      => $builder->toSql(),
            'bindings' => $builder->getBindings(),
        ];

        $this->assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderApply(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'in' => [
                    [
                        'sql'      => 'select * from "tmp" where "property" in (?, ?, ?)',
                        'bindings' => ['abc', 2, 4],
                    ],
                    'property',
                    ['abc', 2, 4],
                    false,
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
