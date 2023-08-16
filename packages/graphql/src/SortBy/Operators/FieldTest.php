<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent\Builder as EloquentHandler;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query\Builder as QueryHandler;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout\Builder as ScoutHandler;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Mockery;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 *
 * @phpstan-import-type BuilderFactory from BuilderDataProvider
 */
#[CoversClass(Field::class)]
class FieldTest extends TestCase {
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
        $operator  = $this->app->make(Field::class);
        $argument  = $argumentFactory($this);
        $directive = $this->app->make(Directive::class);
        $builder   = $builderFactory($this);
        $builder   = $operator->call($directive, $builder, $property, $argument);

        self::assertDatabaseQueryEquals($expected, $builder);
    }

    public function testCallEloquentBuilder(): void {
        $this->useGraphQLSchema('type Query { test: String! @mock}');

        $this->override(EloquentHandler::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('handle')
                ->once();
        });
        $this->override(QueryHandler::class);
        $this->override(ScoutHandler::class);

        $directive = $this->app->make(Directive::class);
        $property  = new Property();
        $operator  = $this->app->make(Field::class);
        $argument  = $this->getGraphQLArgument(
            'Test',
            'asc',
            'enum Test { asc }',
        );
        $builder   = Mockery::mock(EloquentBuilder::class);

        $operator->call($directive, $builder, $property, $argument);
    }

    public function testCallQueryBuilder(): void {
        $this->useGraphQLSchema('type Query { test: String! @mock}');

        $this->override(EloquentHandler::class);
        $this->override(QueryHandler::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('handle')
                ->once();
        });
        $this->override(ScoutHandler::class);

        $directive = $this->app->make(Directive::class);
        $property  = new Property();
        $operator  = $this->app->make(Field::class);
        $argument  = $this->getGraphQLArgument(
            'Test',
            'asc',
        );
        $builder   = Mockery::mock(QueryBuilder::class);

        $operator->call($directive, $builder, $property, $argument);
    }

    public function testCallScoutBuilder(): void {
        $this->useGraphQLSchema('type Query { test: String! @mock}');

        $this->override(EloquentHandler::class);
        $this->override(QueryHandler::class);
        $this->override(ScoutHandler::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('handle')
                ->once();
        });

        $directive = $this->app->make(Directive::class);
        $property  = new Property();
        $operator  = $this->app->make(Field::class);
        $argument  = $this->getGraphQLArgument(
            'Test',
            'asc',
            'enum Test { asc }',
        );
        $builder   = Mockery::mock(ScoutBuilder::class);

        $operator->call($directive, $builder, $property, $argument);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public static function dataProviderCall(): array {
        $factory = static function (self $test): Argument {
            $test->useGraphQLSchema(
                <<<'GRAPHQL'
                type Query {
                    test(input: Test @sortBy): String! @all
                }

                input Test {
                    a: Int!
                    b: String
                }
                GRAPHQL,
            );

            return $test->getGraphQLArgument(
                'SortByTypeDirection!',
                'desc',
            );
        };

        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'property' => [
                    [
                        'query'    => 'select * from "tmp" order by "a" desc',
                        'bindings' => [],
                    ],
                    new Property('a'),
                    $factory,
                ],
            ]),
        ))->getData();
    }
    //</editor-fold>
}
