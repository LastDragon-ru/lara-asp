<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\StartsWith
 */
class StartsWithTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::apply
     *
     * @dataProvider dataProviderApply
     *
     * @param array{query: string, bindings: array<mixed>} $expected
     */
    public function testApply(
        array $expected,
        Closure $builder,
        string $property,
        mixed $value,
    ): void {
        $operator = $this->app->make(StartsWith::class);
        $builder  = $builder($this);
        $builder  = $operator->apply($builder, $property, $value);

        $this->assertDatabaseQueryEquals($expected, $builder);
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
                'startsWith' => [
                    [
                        'query'    => 'select * from "tmp" where "property" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['!%a[!_]c!!!%%'],
                    ],
                    'property',
                    '%a[_]c!%',
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
