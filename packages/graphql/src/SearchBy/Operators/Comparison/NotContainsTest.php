<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\OperatorTests;
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
#[CoversClass(NotContains::class)]
final class NotContainsTest extends TestCase {
    use OperatorTests;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderCall
     *
     * @param array{query: string, bindings: array<array-key, mixed>} $expected
     * @param BuilderFactory                                          $builderFactory
     * @param class-string<Grammar>                                   $grammar
     * @param Closure(static): Argument                               $argumentFactory
     * @param Closure(static): Context|null                           $contextFactory
     */
    public function testCall(
        array $expected,
        Closure $builderFactory,
        string $grammar,
        Property $property,
        Closure $argumentFactory,
        ?Closure $contextFactory,
    ): void {
        $self           = $this;
        $builderFactory = static function () use ($self, $builderFactory, $grammar): mixed {
            $builder = $builderFactory($self);
            $grammar = new $grammar();

            if ($builder instanceof EloquentBuilder) {
                $builder->getQuery()->grammar = $grammar;
            } else {
                $builder->grammar = $grammar;
            }

            return $builder;
        };

        $this->testOperator(Directive::class, $expected, $builderFactory, $property, $argumentFactory, $contextFactory);
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
                MySqlGrammar::class     => [
                    [
                        'query'    => 'select * from `test_objects` where `property` NOT LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    MySqlGrammar::class,
                    new Property('property', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                    null,
                ],
                SQLiteGrammar::class    => [
                    [
                        'query'    => 'select * from "test_objects" where "property" NOT LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    SQLiteGrammar::class,
                    new Property('property', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                    null,
                ],
                PostgresGrammar::class  => [
                    [
                        'query'    => 'select * from "test_objects" where "property" NOT LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    PostgresGrammar::class,
                    new Property('property', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                    null,
                ],
                SqlServerGrammar::class => [
                    [
                        'query'    => 'select * from [test_objects] where [property] NOT LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a![!_!]c!!!%%'],
                    ],
                    SqlServerGrammar::class,
                    new Property('property', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                    null,
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
