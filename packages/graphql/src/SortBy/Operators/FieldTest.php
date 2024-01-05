<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Eloquent\Builder as EloquentSorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Query\Builder as QuerySorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout\Builder as ScoutSorter;
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
        $operator  = Container::getInstance()->make(Field::class);
        $argument  = $argumentFactory($this);
        $directive = Container::getInstance()->make(Directive::class);
        $builder   = $builderFactory($this);
        $builder   = $operator->call($directive, $builder, $property, $argument);

        self::assertDatabaseQueryEquals($expected, $builder);
    }

    public function testCallEloquentBuilder(): void {
        $this->useGraphQLSchema('type Query { test: String! @mock}');

        $this->override(EloquentSorter::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('sort')
                ->once()
                ->andReturns();
        });

        $directive = Container::getInstance()->make(Directive::class);
        $property  = new Property();
        $operator  = Container::getInstance()->make(Field::class);
        $argument  = $this->getGraphQLArgument(
            'Test',
            Direction::Asc,
            'enum Test { asc }',
        );
        $builder   = Mockery::mock(EloquentBuilder::class);

        $operator->call($directive, $builder, $property, $argument);
    }

    public function testCallQueryBuilder(): void {
        $this->useGraphQLSchema('type Query { test: String! @mock}');

        $this->override(QuerySorter::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('sort')
                ->once();
        });

        $directive = Container::getInstance()->make(Directive::class);
        $property  = new Property();
        $operator  = Container::getInstance()->make(Field::class);
        $argument  = $this->getGraphQLArgument(
            'Test',
            Direction::Asc,
        );
        $builder   = Mockery::mock(QueryBuilder::class);

        $operator->call($directive, $builder, $property, $argument);
    }

    public function testCallScoutBuilder(): void {
        $this->useGraphQLSchema('type Query { test: String! @mock}');

        $this->override(ScoutSorter::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('sort')
                ->once();
        });

        $directive = Container::getInstance()->make(Directive::class);
        $property  = new Property();
        $operator  = Container::getInstance()->make(Field::class);
        $argument  = $this->getGraphQLArgument(
            'Test',
            Direction::Asc,
            'enum Test { asc }',
        );
        $builder   = Mockery::mock(ScoutBuilder::class);

        $operator->call($directive, $builder, $property, $argument);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
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
                Direction::Desc,
            );
        };

        return (new CompositeDataProvider(
            new BuilderDataProvider(),
            new ArrayDataProvider([
                'property' => [
                    [
                        'query'    => 'select * from "test_objects" order by "a" desc',
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
