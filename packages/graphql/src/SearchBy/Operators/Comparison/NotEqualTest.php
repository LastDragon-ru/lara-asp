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
 * @covers \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
class NotEqualTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
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
        $operator = $this->app->make(NotEqual::class);
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
                        'query'    => 'select * from "tmp" where "property" != ?',
                        'bindings' => ['abc'],
                    ],
                    new Property('property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', 'abc');
                    },
                ],
                'property.path' => [
                    [
                        'query'    => 'select * from "tmp" where "path"."to"."property" != ?',
                        'bindings' => [123],
                    ],
                    new Property('path', 'to', 'property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('Int!', 123);
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
