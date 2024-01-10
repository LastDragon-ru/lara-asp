<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(Between::class)]
class BetweenTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderCall
     *
     * @param array{query: string, bindings: array<array-key, mixed>} $expected
     * @param BuilderFactory                                          $builderFactory
     * @param Closure(static): Argument                               $argumentFactory
     */
    public function testCall(
        array $expected,
        Closure $builderFactory,
        Property $property,
        Closure $argumentFactory,
    ): void {
        $operator = Container::getInstance()->make(Between::class);
        $property = $property->getChild('operator name should be ignored');
        $argument = $argumentFactory($this);
        $context  = new Context();
        $search   = Mockery::mock(Handler::class);
        $builder  = $builderFactory($this);
        $builder  = $operator->call($search, $builder, $property, $argument, $context);

        self::assertDatabaseQueryEquals($expected, $builder);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderCall(): array {
        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'property'      => [
                    [
                        'query'    => 'select * from "test_objects" where "property" between ? and ?',
                        'bindings' => [1, 2],
                    ],
                    new Property('property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
                ],
                'property.path' => [
                    [
                        'query'    => 'select * from "test_objects" where "path"."to"."property" between ? and ?',
                        'bindings' => [1, 2],
                    ],
                    new Property('path', 'to', 'property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('[Int!]!', [1, 2, 3]);
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
