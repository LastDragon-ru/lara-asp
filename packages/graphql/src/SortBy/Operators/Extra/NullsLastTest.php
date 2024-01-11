<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Extra;

use Closure;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(NullsLast::class)]
class NullsLastTest extends TestCase {
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
        $operator = Container::getInstance()->make(NullsLast::class);
        $property = $property->getChild('operator name should be ignored');
        $argument = $argumentFactory($this);
        $context  = new Context();
        $handler  = Container::getInstance()->make(Directive::class);
        $builder  = $builderFactory($this);
        $builder  = $operator->call($handler, $builder, $property, $argument, $context);

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
                'property' => [
                    [
                        'query'    => 'select * from "test_objects" order by "a" ASC NULLS LAST',
                        'bindings' => [],
                    ],
                    new Property(),
                    static function (self $test): Argument {
                        $test->useGraphQLSchema(
                            <<<'GRAPHQL'
                            type Query {
                                test(input: Test @sortBy): String! @all
                            }

                            input Test {
                                a: String
                            }
                            GRAPHQL,
                        );

                        return $test->getGraphQLArgument(
                            'SortByClauseTest!',
                            [
                                'nullsLast' => [
                                    'a' => Direction::Asc,
                                ],
                            ],
                        );
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
