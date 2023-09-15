<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use Closure;
use Exception;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\BuilderUnknown;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\ArgumentAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FailedToCreateStreamFieldIsNotList;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FailedToCreateStreamFieldIsSubscription;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\TestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Queries\Query;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Types\CustomType;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Types\CustomType\Field;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Car;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\CarEngine;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Mockery;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use stdClass;

use function config;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[CoversClass(Directive::class)]
class DirectiveTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    public function testManipulateFieldDefinition(): void {
        config([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(Car::class))->getNamespaceName(),
            ],
        ]);

        $directives = $this->app->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(self::getTestData()->file('~schema.graphql'));
        $this->assertGraphQLSchemaEquals(
            self::getTestData()->file('~expected.graphql'),
        );
    }

    public function testManipulateFieldDefinitionBuilderUnknown(): void {
        self::expectException(BuilderUnknown::class);
        self::expectExceptionMessage('Impossible to determine builder type for `type Query { field }`.');

        $directives = $this->app->make(DirectiveLocator::class);
        $directive  = new class() extends StreamDirective {
            public function getBuilderInfo(TypeSource $source): ?BuilderInfo {
                return null;
            }
        };

        $directives->setResolved('stream', $directive::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
                field: [Test] @stream(searchable: false, sortable: false)
            }

            type Test {
                id: ID!
            }
            GRAPHQL,
        );
    }

    public function testManipulateFieldDefinitionFieldIsNotList(): void {
        self::expectException(FailedToCreateStreamFieldIsNotList::class);
        self::expectExceptionMessage(
            'Impossible to create a stream for `type Test { field }` because it is not a list.',
        );

        $directives = $this->app->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
                field: Test
            }

            type Test {
                field: Int @stream(searchable: false, sortable: false)
            }
            GRAPHQL,
        );
    }

    public function testManipulateFieldDefinitionArgumentAlreadyDefined(): void {
        self::expectException(ArgumentAlreadyDefined::class);
        self::expectExceptionMessage('Argument `type Test { field(where) }` already defined.');

        $directives = $this->app->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
                field: Test
            }

            type Test {
                field(where: Int): [Test] @stream
            }
            GRAPHQL,
        );
    }

    public function testManipulateFieldDefinitionFieldIsSubscription(): void {
        self::expectException(FailedToCreateStreamFieldIsSubscription::class);
        self::expectExceptionMessage(
            'Impossible to create a stream for `type Subscription { field }` because it is a Subscription.',
        );

        $directives = $this->app->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Subscription {
                field: [Int] @stream
            }
            GRAPHQL,
        );
    }

    /**
     * @dataProvider dataProviderGetBuilderInfo
     *
     * @param Closure():mixed|array{class-string, string}|null $resolver
     */
    public function testGetBuilderInfo(BuilderInfo|null $expected, Closure|array|null $resolver): void {
        $source    = Mockery::mock(ObjectFieldSource::class);
        $directive = Mockery::mock(Directive::class);
        $directive->shouldAllowMockingProtectedMethods();
        $directive->makePartial();
        $directive
            ->shouldReceive('getResolver')
            ->with($source)
            ->once()
            ->andReturn($resolver);

        self::assertEquals($expected, $directive->getBuilderInfo($source));
    }

    public function testGetResolver(): void {
        // Prepare
        $parent    = new ObjectSource(
            Mockery::mock(AstManipulator::class)->makePartial(),
            new ObjectType(['name' => 'Car', 'fields' => []]),
        );
        $directive = Mockery::mock(Directive::class);
        $directive->shouldAllowMockingProtectedMethods();
        $directive->makePartial();

        // Query
        $source   = $parent->getField(Parser::fieldDefinition('query: String'));
        $expected = [stdClass::class, 'method'];

        $directive
            ->shouldReceive('getResolverQuery')
            ->with($parent->getTypeName(), $source->getName())
            ->once()
            ->andReturns($expected);

        self::assertEquals($expected, $directive->getResolver($source));

        // Type
        $source   = $parent->getField(Parser::fieldDefinition('type: String'));
        $expected = static function (mixed $root, array $args, GraphQLContext $context, ResolveInfo $info): mixed {
            return null;
        };

        $directive
            ->shouldReceive('getResolverQuery')
            ->with($parent->getTypeName(), $source->getName())
            ->once()
            ->andReturns(null);
        $directive
            ->shouldReceive('getResolverRelation')
            ->with($parent->getTypeName(), $source->getName())
            ->once()
            ->andReturn($expected);

        self::assertSame($expected, $directive->getResolver($source));

        // Root type
        $parent   = new ObjectSource(
            Mockery::mock(AstManipulator::class)->makePartial(),
            new ObjectType(['name' => 'Query', 'fields' => []]),
        );
        $source   = $parent->getField(Parser::fieldDefinition('root: String'));
        $expected = static function (mixed $root, array $args, GraphQLContext $context, ResolveInfo $info): mixed {
            return null;
        };

        $directive
            ->shouldReceive('getResolverQuery')
            ->with($parent->getTypeName(), $source->getName())
            ->once()
            ->andReturns(null);
        $directive
            ->shouldReceive('getResolverModel')
            ->with($source->getTypeName())
            ->once()
            ->andReturn($expected);

        self::assertSame($expected, $directive->getResolver($source));
    }

    /**
     * @dataProvider dataProviderGetResolverExplicit
     *
     * @param array{method: string, args: array<array-key, mixed>}|null $expected
     */
    public function testGetResolverExplicit(array|null $expected, string $arguments): void {
        // Prepare
        config([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(Car::class))->getNamespaceName(),
            ],
        ]);

        $field     = Parser::fieldDefinition('test: String');
        $source    = new ObjectFieldSource(
            Mockery::mock(AstManipulator::class)->makePartial(),
            new ObjectType(['name' => 'Car', 'fields' => []]),
            $field,
        );
        $directive = Mockery::mock(Directive::class);
        $directive->shouldAllowMockingProtectedMethods();
        $directive->makePartial();
        $directive->hydrate(
            Parser::directive("@stream(builder: {$arguments})"),
            $field,
        );

        if ($expected !== null) {
            $directive
                ->shouldReceive($expected['method'])
                ->withArgs($expected['args'])
                ->once()
                ->andReturns();
        }

        $actual = $directive->getResolver($source);

        if ($expected === null) {
            self::assertNull($actual);
        }
    }

    public function testGetResolverRelation(): void {
        // Prepare
        config([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(Car::class))->getNamespaceName(),
            ],
        ]);

        $args      = [];
        $info      = Mockery::mock(ResolveInfo::class);
        $context   = Mockery::mock(GraphQLContext::class);
        $namespace = json_encode((new ReflectionClass(TestObject::class))->getNamespaceName(), JSON_THROW_ON_ERROR);
        $directive = new class() extends Directive {
            public function name(): string {
                return 'stream';
            }

            public function getResolverRelation(string $model, string $relation): ?Closure {
                return parent::getResolverRelation($model, $relation);
            }
        };

        $directive->hydrate(
            Parser::directive('@stream'),
            Parser::fieldDefinition("test: String @namespace(stream: {$namespace})"),
        );

        // Unknown
        self::assertNull($directive->getResolverRelation('Car', 'unknown'));

        // Known
        $resolver = $directive->getResolverRelation('Car', 'engine');

        self::assertNotNull($resolver);
        self::assertDatabaseQueryEquals(
            [
                'query'    => <<<'SQL'
                    select
                        *
                    from
                        "car_engines"
                    where
                        "car_engines"."foreignKey" = ?
                        and "car_engines"."foreignKey" is not null
                        and "installed" = ?
                    SQL
                ,
                'bindings' => [
                    Car::Id,
                    1,
                ],
            ],
            $resolver(
                new Car([
                    'localKey' => Car::Id,
                ]),
                $args,
                $context,
                $info,
            ),
        );

        // Wrong model
        $resolver = $directive->getResolverRelation('Car', 'engine');

        self::assertNotNull($resolver);
        self::assertDatabaseQueryEquals(
            [
                'query'    => <<<'SQL'
                    select
                        *
                    from
                        "car_engines"
                    where
                        "installed" = ?
                        and 0 = 1
                    SQL
                ,
                'bindings' => [
                    1,
                ],
            ],
            $resolver(
                new CarEngine([
                    'localKey' => Car::Id,
                ]),
                $args,
                $context,
                $info,
            ),
        );
    }

    public function testGetResolverQuery(): void {
        // Prepare
        config([
            'lighthouse.namespaces.queries' => [
                (new ReflectionClass(Query::class))->getNamespaceName(),
            ],
            'lighthouse.namespaces.types'   => [
                (new ReflectionClass(CustomType::class))->getNamespaceName(),
            ],
        ]);

        $directive = new class() extends Directive {
            public function name(): string {
                return 'stream';
            }

            /**
             * @inheritDoc
             */
            public function getResolverQuery(string $type, string $field): ?array {
                return parent::getResolverQuery($type, $field);
            }
        };

        $directive->hydrate(
            Parser::directive('@stream'),
            Parser::fieldDefinition('test: String'),
        );

        // Test
        self::assertEquals(
            [Query::class, '__invoke'],
            $directive->getResolverQuery('Query', 'query'),
        );
        self::assertEquals(
            [Field::class, '__invoke'],
            $directive->getResolverQuery('CustomType', 'field'),
        );
    }

    public function testGetResolverModel(): void {
        // Prepare
        config([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(Car::class))->getNamespaceName(),
            ],
        ]);

        $root      = null;
        $args      = [];
        $info      = Mockery::mock(ResolveInfo::class);
        $context   = Mockery::mock(GraphQLContext::class);
        $namespace = json_encode((new ReflectionClass(TestObject::class))->getNamespaceName(), JSON_THROW_ON_ERROR);
        $directive = new class() extends Directive {
            public function name(): string {
                return 'stream';
            }

            public function getResolverModel(string $model): Closure {
                return parent::getResolverModel($model);
            }
        };

        $directive->hydrate(
            Parser::directive('@stream'),
            Parser::fieldDefinition("test: String @namespace(stream: {$namespace})"),
        );

        // Test
        self::assertEquals(
            TestObject::class,
            ($directive->getResolverModel('TestObject'))($root, $args, $context, $info)->getModel()::class,
        );
        self::assertEquals(
            TestObject::class,
            ($directive->getResolverModel(TestObject::class))($root, $args, $context, $info)->getModel()::class,
        );
        self::assertEquals(
            Car::class,
            ($directive->getResolverModel('Car'))($root, $args, $context, $info)->getModel()::class,
        );
        self::assertEquals(
            Car::class,
            ($directive->getResolverModel(Car::class))($root, $args, $context, $info)->getModel()::class,
        );
    }

    public function testGetResolverClass(): void {
        // Prepare
        $namespace = json_encode(__NAMESPACE__, JSON_THROW_ON_ERROR);
        $directive = new class() extends Directive {
            public function name(): string {
                return 'stream';
            }

            /**
             * @inheritDoc
             */
            public function getResolverClass(string $class): array {
                return parent::getResolverClass($class);
            }
        };

        $directive->hydrate(
            Parser::directive('@stream'),
            Parser::fieldDefinition("test: String @namespace(stream: {$namespace})"),
        );

        // Test
        self::assertEquals(
            [self::class, '__invoke'],
            $directive->getResolverClass('DirectiveTest'),
        );
        self::assertEquals(
            [stdClass::class, '__invoke'],
            $directive->getResolverClass(stdClass::class),
        );
        self::assertEquals(
            [stdClass::class, 'method'],
            $directive->getResolverClass(stdClass::class.'@method'),
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{BuilderInfo|null, Closure():mixed|array{string, string}|null}>
     */
    public static function dataProviderGetBuilderInfo(): array {
        $class = new class() {
            /**
             * @return EloquentBuilder<EloquentModel>
             */
            public function method(): EloquentBuilder {
                throw new Exception('Should not be called.');
            }

            public function union(): stdClass|DirectiveTest {
                throw new Exception('Should not be called.');
            }
        };

        return [
            'null'                        => [
                null,
                null,
            ],
            'Closure(): mixed'            => [
                null,
                static function (): mixed {
                    return null;
                },
            ],
            'Closure(): class'            => [
                BuilderInfo::create(EloquentBuilder::class),
                static function (): EloquentBuilder {
                    throw new Exception('Should not be called.');
                },
            ],
            'Closure(): union'            => [
                null,
                static function () use ($class): stdClass|self {
                    return $class->union();
                },
            ],
            'array(Unknown, method)'      => [
                null,
                ['Unknown', 'method'],
            ],
            'array(Class, unknownMethod)' => [
                null,
                [stdClass::class, 'unknownMethod'],
            ],
            'array(Class, method: union)' => [
                null,
                [$class::class, 'union'],
            ],
            'array(Class, method)'        => [
                BuilderInfo::create(EloquentBuilder::class),
                [$class::class, 'method'],
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      array{method: string, args: array<array-key, mixed>}|null,
     *      string,
     *      }>
     */
    public static function dataProviderGetResolverExplicit(): array {
        return [
            'empty'    => [
                null,
                '{}',
            ],
            'builder'  => [
                [
                    'method' => 'getResolverClass',
                    'args'   => [
                        'BuilderClass',
                    ],
                ],
                '{builder: "BuilderClass"}',
            ],
            'model'    => [
                [
                    'method' => 'getResolverModel',
                    'args'   => [
                        'ModelClass',
                    ],
                ],
                '{model: "ModelClass"}',
            ],
            'relation' => [
                [
                    'method' => 'getResolverRelation',
                    'args'   => [
                        'Car',
                        'engine',
                    ],
                ],
                '{relation: "engine"}',
            ],
        ];
    }
    //</editor-fold>
}
