<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Package\OperatorTests;
use LastDragon_ru\LaraASP\GraphQL\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function implode;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(Contains::class)]
final class ContainsTest extends TestCase {
    use OperatorTests;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array{query: string, bindings: array<array-key, mixed>} $expected
     * @param BuilderFactory                                          $builderFactory
     * @param class-string<Grammar>                                   $grammar
     * @param Closure(static): Argument                               $argumentFactory
     * @param Closure(static): Context|null                           $contextFactory
     * @param Closure(object, Field): string|null                     $resolver
     */
    #[DataProvider('dataProviderCall')]
    public function testCall(
        array $expected,
        Closure $builderFactory,
        string $grammar,
        Field $field,
        Closure $argumentFactory,
        ?Closure $contextFactory,
        ?Closure $resolver,
    ): void {
        $self           = $this;
        $builderFactory = static function () use ($self, $builderFactory, $grammar): mixed {
            $connection = Mockery::mock(Connection::class);
            $connection
                ->shouldReceive('getTablePrefix')
                ->atLeast()
                ->once()
                ->andReturn('');

            $builder = $builderFactory($self);
            $grammar = new $grammar($connection);

            if ($builder instanceof EloquentBuilder) {
                $builder->getQuery()->grammar = $grammar;
            } else {
                $builder->grammar = $grammar;
            }

            return $builder;
        };

        $this->testDatabaseOperator(
            Directive::class,
            $expected,
            $builderFactory,
            $field,
            $argumentFactory,
            $contextFactory,
            $resolver,
        );
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
                        'query'    => 'select * from `test_objects` where `field` LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    MySqlGrammar::class,
                    new Field('field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                    null,
                    null,
                ],
                SQLiteGrammar::class    => [
                    [
                        'query'    => 'select * from "test_objects" where "field" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    SQLiteGrammar::class,
                    new Field('field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                    null,
                    null,
                ],
                PostgresGrammar::class  => [
                    [
                        'query'    => 'select * from "test_objects" where "field" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    PostgresGrammar::class,
                    new Field('field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                    null,
                    null,
                ],
                SqlServerGrammar::class => [
                    [
                        'query'    => 'select * from [test_objects] where [field] LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a![!_!]c!!!%%'],
                    ],
                    SqlServerGrammar::class,
                    new Field('field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                    null,
                    null,
                ],
                'field.path'            => [
                    [
                        'query'    => 'select * from "test_objects" where "path"."to"."field" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    SQLiteGrammar::class,
                    new Field('path', 'to', 'field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                    null,
                    null,
                ],
                'resolver'              => [
                    [
                        'query'    => 'select * from "test_objects" where "path__to__field" LIKE ? ESCAPE \'!\'',
                        'bindings' => ['%!%a[!_]c!!!%%'],
                    ],
                    SQLiteGrammar::class,
                    new Field('path', 'to', 'field', 'operator name should be ignored'),
                    static function (self $test): Argument {
                        return $test->getGraphQLArgument('String!', '%a[_]c!%');
                    },
                    null,
                    static function (object $builder, Field $field): string {
                        return implode('__', $field->getPath());
                    },
                ],
            ]),
        ))->getData();
    }
    // </editor-fold>
}
