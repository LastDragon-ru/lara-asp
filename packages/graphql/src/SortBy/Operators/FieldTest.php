<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use Closure;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Sorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\EloquentSorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\QuerySorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\ScoutSorter;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\DataProviders\BuilderDataProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use Mockery;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function config;

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
                ->shouldReceive('isNullsSupported')
                ->once()
                ->andReturn(false);
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
            'enum Test { Asc }',
        );
        $builder   = Mockery::mock(EloquentBuilder::class);

        $operator->call($directive, $builder, $property, $argument);
    }

    public function testCallQueryBuilder(): void {
        $this->useGraphQLSchema('type Query { test: String! @mock}');

        $this->override(QuerySorter::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('isNullsSupported')
                ->once()
                ->andReturn(false);
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
                ->shouldReceive('isNullsSupported')
                ->once()
                ->andReturn(false);
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
            'enum Test { Asc }',
        );
        $builder   = Mockery::mock(ScoutBuilder::class);

        $operator->call($directive, $builder, $property, $argument);
    }

    /**
     * @dataProvider dataProviderGetNulls
     *
     * @param array<string, mixed>            $config
     * @param Closure(static): Sorter<object> $sorterFactory
     */
    public function testGetNulls(?Nulls $expected, ?array $config, Closure $sorterFactory, Direction $direction): void {
        if ($config) {
            config($config);
        }

        $sorter   = $sorterFactory($this);
        $property = new Property();
        $operator = Mockery::mock(Field::class);
        $operator->shouldAllowMockingProtectedMethods();
        $operator->makePartial();

        self::assertSame($expected, $operator->getNulls($sorter, $property, $direction));
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

    /**
     * @return array<string, array{?Nulls, ?array<string, mixed>, Closure(static): Sorter<object>, Direction}>
     */
    public static function dataProviderGetNulls(): array {
        $key              = Package::Name.'.sort_by.nulls';
        $getSorterFactory = static function (bool $nullsSortable): Closure {
            return static function () use ($nullsSortable): Sorter {
                return new class($nullsSortable) implements Sorter {
                    public function __construct(
                        private readonly bool $nullsSortable,
                    ) {
                        // empty
                    }

                    #[Override]
                    public function isNullsSupported(): bool {
                        return $this->nullsSortable;
                    }

                    #[Override]
                    public function sort(
                        object $builder,
                        Property $property,
                        Direction $direction,
                        Nulls $nulls = null,
                    ): object {
                        throw new Exception('should not be called.');
                    }
                };
            };
        };

        return [
            'default'                                    => [
                null,
                null,
                $getSorterFactory(true),
                Direction::Asc,
            ],
            'nulls are not sortable'                     => [
                null,
                [
                    $key => Nulls::First,
                ],
                $getSorterFactory(false),
                Direction::Asc,
            ],
            'nulls are sortable (asc)'                   => [
                Nulls::Last,
                [
                    $key => Nulls::Last,
                ],
                $getSorterFactory(true),
                Direction::Asc,
            ],
            'nulls are sortable (desc)'                  => [
                Nulls::Last,
                [
                    $key => Nulls::Last,
                ],
                $getSorterFactory(true),
                Direction::Desc,
            ],
            'nulls are sortable (separate)'              => [
                Nulls::First,
                [
                    $key => [
                        Direction::Asc->value  => Nulls::Last,
                        Direction::Desc->value => Nulls::First,
                    ],
                ],
                $getSorterFactory(true),
                Direction::Desc,
            ],
            '(deprecated) nulls are sortable (asc)'      => [
                Nulls::Last,
                [
                    $key => Nulls::Last,
                ],
                $getSorterFactory(true),
                Direction::asc,
            ],
            '(deprecated) nulls are sortable (desc)'     => [
                Nulls::Last,
                [
                    $key => Nulls::Last,
                ],
                $getSorterFactory(true),
                Direction::desc,
            ],
            '(deprecated) nulls are sortable (separate)' => [
                Nulls::First,
                [
                    $key => [
                        Direction::Asc->value  => Nulls::Last,
                        Direction::Desc->value => Nulls::First,
                    ],
                ],
                $getSorterFactory(true),
                Direction::desc,
            ],
        ];
    }
    //</editor-fold>
}
