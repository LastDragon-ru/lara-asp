<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\StartsWith
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
class StartsWithTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::call
     *
     * @dataProvider dataProviderCall
     *
     * @param array{query: string, bindings: array<mixed>} $expected
     * @param BuilderFactory                               $builderFactory
     * @param Closure(static): Argument                    $argumentFactory
     */
    public function testCall(
        array $expected,
        Closure $builderFactory,
        Property $property,
        Closure $argumentFactory,
    ): void {
        $operator = $this->app->make(StartsWith::class);
        $property = $property->getChild('operator name should be ignored');
        $argument = $argumentFactory($this);
        $search   = Mockery::mock(Handler::class);
        $builder  = $builderFactory($this);
        $builder  = $operator->call($search, $builder, $property, $argument);

        self::assertDatabaseQueryEquals($expected, $builder);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderCall(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'property'      => [
                    [
                        'query'    => 'select * from "tmp" where "property" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['!%a[!_]c!!!%%'],
                    ],
                    new Property('property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                ],
                'property.path' => [
                    [
                        'query'    => 'select * from "tmp" where "path"."to"."property" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['abc%'],
                    ],
                    new Property('path', 'to', 'property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', 'abc');
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
