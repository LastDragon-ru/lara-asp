<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Builder;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Contains
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
class ContainsTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::call
     * @covers ::escape
     *
     * @dataProvider dataProviderCall
     *
     * @param array{query: string, bindings: array<mixed>} $expected
     * @param BuilderFactory                               $builderFactory
     * @param class-string<Grammar>                        $grammar
     * @param Closure(static): Argument                    $argumentFactory
     */
    public function testCall(
        array $expected,
        Closure $builderFactory,
        string $grammar,
        Property $property,
        Closure $argumentFactory,
    ): void {
        $builder = $builderFactory($this);
        $grammar = new $grammar();

        if ($builder instanceof EloquentBuilder) {
            $builder->toBase()->grammar = $grammar;
        } else {
            $builder->grammar = $grammar;
        }

        $operator = $this->app->make(Contains::class);
        $argument = $argumentFactory($this);
        $search   = Mockery::mock(Builder::class);
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
                MySqlGrammar::class     => [
                    [
                        'query'    => 'select * from `tmp` where `property` LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    MySqlGrammar::class,
                    new Property('property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                ],
                SQLiteGrammar::class    => [
                    [
                        'query'    => 'select * from "tmp" where "property" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    SQLiteGrammar::class,
                    new Property('property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                ],
                PostgresGrammar::class  => [
                    [
                        'query'    => 'select * from "tmp" where "property" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    PostgresGrammar::class,
                    new Property('property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                ],
                SqlServerGrammar::class => [
                    [
                        'query'    => 'select * from [tmp] where [property] LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a![!_!]c!!!%%'],
                    ],
                    SqlServerGrammar::class,
                    new Property('property'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
