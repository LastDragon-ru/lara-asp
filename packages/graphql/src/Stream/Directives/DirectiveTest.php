<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use Closure;
use Exception;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Encryption\StringEncrypter;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\BuilderUnknown;
use LastDragon_ru\LaraASP\GraphQL\Builder\ManipulatorFactory;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\ArgumentAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\FieldArgumentDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\StreamFactory;
use LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamOffsetDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\ArgumentMissed;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\BuilderInvalid;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\BuilderUnsupported;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\Client\ArgumentsMutuallyExclusive;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FieldIsNotList;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FieldIsSubscription;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FieldIsUnion;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\KeyUnknown;
use LastDragon_ru\LaraASP\GraphQL\Stream\Offset as StreamOffset;
use LastDragon_ru\LaraASP\GraphQL\Stream\Streams\Stream;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\TestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\TestObjectSearchable;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\WithTestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Queries\Query;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Types\CustomType;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Types\CustomType\Field;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Car;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\CarEngine;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Requirements\RequiresLaravelScout;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesFragment;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies\JsonBody;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use Mockery;
use Mockery\MockInterface;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSetFactory;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Scout\ScoutServiceProvider;
use Nuwave\Lighthouse\Support\Contracts\Directive as DirectiveContract;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use stdClass;

use function array_merge;
use function is_string;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[CoversClass(Directive::class)]
final class DirectiveTest extends TestCase {
    use WithTestObject;
    use MakesGraphQLRequests;

    // <editor-fold desc="Prepare">
    // =========================================================================
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            ScoutServiceProvider::class,
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<string,mixed>|string $expected
     * @param Closure(static): void      $factory
     * @param array<string, mixed>|null  $where
     * @param array<int, mixed>|null     $order
     */
    #[DataProvider('dataProviderDirective')]
    public function testDirective(
        array|string $expected,
        Closure $factory,
        ?array $where,
        ?array $order,
        int $limit,
        string|int|null $offset,
    ): void {
        // Dependencies
        if (!is_string($expected)) {
            $this->override(StringEncrypter::class, DirectiveTest_Encrypter::class);
        }

        // Prepare
        $path = is_string($expected) ? 'errors.0.message' : 'data.test';
        $body = is_string($expected)
            ? json_encode($expected, JSON_THROW_ON_ERROR)
            : $expected;

        $factory($this);

        // Dependencies
        $this
            ->useGraphQLSchema(
                <<<'GRAPHQL'
                type Query {
                    test: [TestObject!]! @stream
                }

                type TestObject {
                    id: ID!
                    value: String
                }
                GRAPHQL,
            )
            ->graphQL(
                <<<'GRAPHQL'
                query test(
                    $where: SearchByRootTestObject,
                    $order: [SortByRootTestObject!],
                    $limit: Int!,
                    $offset: StreamOffset,
                ) {
                    test(where: $where, order: $order, limit: $limit, offset: $offset) {
                        items {
                            id
                            value
                        }
                        length
                        navigation {
                            previous
                            current
                            next
                        }
                    }
                }
                GRAPHQL,
                [
                    'where'  => $where,
                    'order'  => $order,
                    'limit'  => $limit,
                    'offset' => $offset,
                ],
            )
            ->assertThat(
                new Response(
                    new Ok(),
                    new JsonContentType(),
                    new JsonBody(
                        new JsonMatchesFragment($path, $body),
                    ),
                ),
            );
    }

    /**
     * @param array<string,mixed>|string $expected
     * @param Closure(static): void      $factory
     * @param array<string, mixed>|null  $where
     * @param array<int, mixed>|null     $order
     */
    #[DataProvider('dataProviderDirective')]
    #[RequiresLaravelScout]
    public function testDirectiveScout(
        array|string $expected,
        Closure $factory,
        ?array $where,
        ?array $order,
        int $limit,
        string|int|null $offset,
    ): void {
        // Config
        $this->setConfig([
            'scout.driver' => 'database',
        ]);

        // Dependencies
        if (!is_string($expected)) {
            $this->override(StringEncrypter::class, DirectiveTest_Encrypter::class);
        }

        // Prepare
        $path = is_string($expected) ? 'errors.0.message' : 'data.test';
        $body = is_string($expected)
            ? json_encode($expected, JSON_THROW_ON_ERROR)
            : $expected;

        $factory($this);

        // Dependencies
        $this
            ->useGraphQLSchema(
                <<<'GRAPHQL'
                type Query {
                    test(search: String! @search): [TestObjectSearchable!]! @stream
                }

                type TestObjectSearchable {
                    id: ID!
                    value: String
                }
                GRAPHQL,
            )
            ->graphQL(
                <<<'GRAPHQL'
                query test(
                    $search: String!,
                    $where: SearchByScoutRootTestObjectSearchable,
                    $order: [SortByScoutRootTestObjectSearchable!],
                    $limit: Int!,
                    $offset: StreamOffset,
                ) {
                    test(search: $search, where: $where, order: $order, limit: $limit, offset: $offset) {
                        items {
                            id
                            value
                        }
                        length
                        navigation {
                            previous
                            current
                            next
                        }
                    }
                }
                GRAPHQL,
                [
                    'search' => '*',
                    'where'  => $where,
                    'order'  => $order,
                    'limit'  => $limit,
                    'offset' => $offset,
                ],
            )
            ->assertThat(
                new Response(
                    new Ok(),
                    new JsonContentType(),
                    new JsonBody(
                        new JsonMatchesFragment($path, $body),
                    ),
                ),
            );
    }

    public function testManipulateFieldDefinition(): void {
        $this->setConfig([
            'lighthouse.namespaces.models'       => [
                (new ReflectionClass(TestObject::class))->getNamespaceName(),
            ],
            Package::Name.'.search_by.operators' => [
                Operators::ID => [
                    SearchByOperatorEqualDirective::class,
                ],
            ],
        ]);

        $this->useGraphQLSchema(self::getTestData()->file('~schema.graphql'));
        $this->assertGraphQLSchemaEquals(
            self::getTestData()->file('~schema-expected.graphql'),
        );
        $this->assertGraphQLSchemaValid();
    }

    #[RequiresLaravelScout]
    public function testManipulateFieldDefinitionScoutBuilder(): void {
        $this->setConfig([
            'lighthouse.namespaces.models'       => [
                (new ReflectionClass(TestObject::class))->getNamespaceName(),
            ],
            Package::Name.'.search_by.operators' => [
                Operators::ID => [
                    SearchByOperatorEqualDirective::class,
                ],
            ],
        ]);

        $directives = $this->app()->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(self::getTestData()->file('~scout.graphql'));
        $this->assertGraphQLSchemaEquals(
            self::getTestData()->file('~scout-expected.graphql'),
        );
        $this->assertGraphQLSchemaValid();
    }

    public function testManipulateFieldDefinitionBuilderUnknown(): void {
        self::expectException(BuilderUnknown::class);
        self::expectExceptionMessage('Impossible to determine builder type for `type Query { field }`.');

        $directives         = $this->app()->make(DirectiveLocator::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $manipulatorFactory = Mockery::mock(ManipulatorFactory::class);
        $directive          = new class($container, $config, $factory, $manipulatorFactory) extends StreamDirective {
            #[Override]
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
        self::expectException(FieldIsNotList::class);
        self::expectExceptionMessage(
            'The `type Test { field }` is not a list.',
        );

        $directives = $this->app()->make(DirectiveLocator::class);

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
        self::expectExceptionMessage('Argument `type Query { field(where) }` already defined.');

        $directives = $this->app()->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
                field(where: Int): [TestObject] @stream
            }

            type TestObject {
                id: ID!
            }
            GRAPHQL,
        );
    }

    public function testManipulateFieldDefinitionFieldIsSubscription(): void {
        self::expectException(FieldIsSubscription::class);
        self::expectExceptionMessage(
            'The `type Subscription { field }` is a Subscription. Subscriptions are not supported.',
        );

        $directives = $this->app()->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Subscription {
                field: [Int] @stream
            }
            GRAPHQL,
        );
    }

    public function testManipulateFieldDefinitionFieldIsUnion(): void {
        self::expectException(FieldIsUnion::class);
        self::expectExceptionMessage(
            'The `type Query { field }` us a union. Unions are not supported.',
        );

        $directives = $this->app()->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
                field: [AB] @stream
            }

            union AB = A | B

            type A {
                id: ID!
            }

            type B {
                id: ID!
            }
            GRAPHQL,
        );
    }

    /**
     * @param Closure(AstManipulator): (ObjectFieldSource|InterfaceFieldSource) $sourceFactory
     * @param Closure():mixed|array{class-string, string}|null                  $resolver
     */
    #[DataProvider('dataProviderGetBuilderInfo')]
    public function testGetBuilderInfo(
        Exception|BuilderInfo|null $expected,
        Closure $sourceFactory,
        Closure|array|null $resolver,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $manipulator        = $manipulatorFactory->create(Mockery::mock(DocumentAST::class));
        $source             = $sourceFactory($manipulator);
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = $this->app()->make(StreamFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $directive          = Mockery::mock(Directive::class, [$container, $config, $factory, $manipulatorFactory]);
        $directive->shouldAllowMockingProtectedMethods();
        $directive->makePartial();
        $directive
            ->shouldReceive('getResolver')
            ->with($source)
            ->once()
            ->andReturn($resolver);

        self::assertEquals($expected, $directive->getBuilderInfo($source));
    }

    public function testGetBuilder(): void {
        $this->setConfig([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(Car::class))->getNamespaceName(),
            ],
        ]);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
                field: [Car] @stream
            }

            type Car {
                id: ID!
            }
            GRAPHQL,
        );

        $type  = $this->getGraphQLSchema()->getQueryType();
        $field = $type?->findField('field');

        self::assertNotNull($type);
        self::assertNotNull($field);
        self::assertNotNull($field->astNode);

        $this->override(SearchByDirective::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('hydrate')
                ->once()
                ->andReturns();
            $mock
                ->shouldReceive('enhance')
                ->once()
                ->andReturnUsing(
                    static fn (object $builder) => $builder,
                );
        });

        $info                  = Mockery::mock(ResolveInfo::class);
        $info->path            = ['field'];
        $info->parentType      = $type;
        $info->fieldDefinition = $field;
        $info->argumentSet     = $this->app()->make(ArgumentSetFactory::class)->fromResolveInfo(
            [
                'limit' => 10,
                'where' => [
                    'field' => [
                        'id' => [
                            'equal' => 123,
                        ],
                    ],
                ],
            ],
            $info,
        );
        $info
            ->shouldReceive('enhanceBuilder')
            ->never();

        $root      = 123;
        $args      = $info->argumentSet->toArray();
        $context   = Mockery::mock(GraphQLContext::class);
        $builder   = Car::query();
        $directive = Mockery::mock(Directive::class);
        $directive->shouldAllowMockingProtectedMethods();
        $directive->makePartial();

        $directive->hydrate(
            Parser::directive('@stream'),
            $field->astNode,
        );

        self::assertInstanceOf($builder::class, $directive->getBuilder($builder, $root, $args, $context, $info));
    }

    #[RequiresLaravelScout]
    public function testGetBuilderScoutBuilder(): void {
        $this->setConfig([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(TestObjectSearchable::class))->getNamespaceName(),
            ],
        ]);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
                field(search: String! @search): [TestObject] @stream
            }

            type TestObject {
                id: ID!
            }
            GRAPHQL,
        );

        $type  = $this->getGraphQLSchema()->getQueryType();
        $field = $type?->findField('field');

        self::assertNotNull($type);
        self::assertNotNull($field);
        self::assertNotNull($field->astNode);

        $this->override(SearchByDirective::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('hydrate')
                ->once()
                ->andReturns();
            $mock
                ->shouldReceive('enhance')
                ->never();
        });

        $info                  = Mockery::mock(ResolveInfo::class);
        $info->path            = ['field'];
        $info->parentType      = $type;
        $info->fieldDefinition = $field;
        $info->argumentSet     = $this->app()->make(ArgumentSetFactory::class)->fromResolveInfo(
            [
                'search' => '*',
            ],
            $info,
        );
        $info
            ->shouldReceive('enhanceBuilder')
            ->never();

        $root      = 123;
        $args      = $info->argumentSet->toArray();
        $context   = Mockery::mock(GraphQLContext::class);
        $builder   = TestObjectSearchable::query();
        $directive = Mockery::mock(Directive::class);
        $directive->shouldAllowMockingProtectedMethods();
        $directive->makePartial();

        $directive->hydrate(
            Parser::directive('@stream'),
            $field->astNode,
        );

        self::assertInstanceOf(ScoutBuilder::class, $directive->getBuilder($builder, $root, $args, $context, $info));
    }

    public function testGetBuilderLighthouseEnhancer(): void {
        $this->setConfig([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(Car::class))->getNamespaceName(),
            ],
        ]);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
                field(id: String! @eq): [Car] @stream
            }

            type Car {
                id: ID!
            }
            GRAPHQL,
        );

        $type  = $this->getGraphQLSchema()->getQueryType();
        $field = $type?->findField('field');

        self::assertNotNull($type);
        self::assertNotNull($field);
        self::assertNotNull($field->astNode);

        $this->override(SearchByDirective::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('hydrate')
                ->once()
                ->andReturns();
            $mock
                ->shouldReceive('enhance')
                ->once()
                ->andReturnUsing(
                    static fn (object $builder) => $builder,
                );
        });

        $info                  = Mockery::mock(ResolveInfo::class);
        $info->path            = ['field'];
        $info->parentType      = $type;
        $info->fieldDefinition = $field;
        $info->argumentSet     = $this->app()->make(ArgumentSetFactory::class)->fromResolveInfo(
            [
                'id'    => 123,
                'where' => [
                    'field' => [
                        'id' => [
                            'equal' => 123,
                        ],
                    ],
                ],
            ],
            $info,
        );
        $info
            ->shouldReceive('enhanceBuilder')
            ->once()
            ->andReturnUsing(
                static fn (object $builder) => $builder,
            );

        $root      = 123;
        $args      = $info->argumentSet->toArray();
        $context   = Mockery::mock(GraphQLContext::class);
        $builder   = Car::query();
        $directive = Mockery::mock(Directive::class);
        $directive->shouldAllowMockingProtectedMethods();
        $directive->makePartial();

        $directive->hydrate(
            Parser::directive('@stream'),
            $field->astNode,
        );

        self::assertInstanceOf($builder::class, $directive->getBuilder($builder, $root, $args, $context, $info));
    }

    /**
     * @param Closure(AstManipulator): (ObjectFieldSource|InterfaceFieldSource) $sourceFactory
     * @param Closure():mixed|array{class-string, string}|null                  $resolver
     */
    #[DataProvider('dataProviderGetBuilderInfoScoutBuilder')]
    #[RequiresLaravelScout]
    public function testGetBuilderInfoScoutBuilder(
        Exception|BuilderInfo|null $expected,
        Closure $sourceFactory,
        Closure|array|null $resolver,
    ): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $manipulator        = $manipulatorFactory->create(Mockery::mock(DocumentAST::class));
        $source             = $sourceFactory($manipulator);
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = $this->app()->make(StreamFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $directive          = Mockery::mock(Directive::class, [$container, $config, $factory, $manipulatorFactory]);
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
        $parent             = new ObjectSource(
            Mockery::mock(AstManipulator::class)->makePartial(),
            new ObjectType(['name' => 'Car', 'fields' => []]),
        );
        $config             = Mockery::mock(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $directive          = Mockery::mock(Directive::class, [$container, $config, $factory, $manipulatorFactory]);
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
     * @param array{method: string, args: array<array-key, mixed>}|null $expected
     */
    #[DataProvider('dataProviderGetResolverExplicit')]
    public function testGetResolverExplicit(array|null $expected, string $arguments): void {
        // Prepare
        $this->setConfig([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(Car::class))->getNamespaceName(),
            ],
        ]);

        $field              = Parser::fieldDefinition('test: String');
        $object             = new ObjectSource(
            Mockery::mock(AstManipulator::class)->makePartial(),
            new ObjectType(['name' => 'Car', 'fields' => []]),
        );
        $source             = $object->getField($field);
        $config             = Mockery::mock(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $directive          = Mockery::mock(Directive::class, [$container, $config, $factory, $manipulatorFactory]);
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
        $this->setConfig([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(Car::class))->getNamespaceName(),
            ],
        ]);

        $args               = [];
        $info               = Mockery::mock(ResolveInfo::class);
        $context            = Mockery::mock(GraphQLContext::class);
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $namespace          = json_encode(
            (new ReflectionClass(TestObject::class))->getNamespaceName(),
            JSON_THROW_ON_ERROR,
        );
        $directive          = new class($container, $config, $factory, $manipulatorFactory) extends Directive {
            #[Override]
            public function name(): string {
                return 'stream';
            }

            #[Override]
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
        $this->setConfig([
            'lighthouse.namespaces.queries' => [
                (new ReflectionClass(Query::class))->getNamespaceName(),
            ],
            'lighthouse.namespaces.types'   => [
                (new ReflectionClass(CustomType::class))->getNamespaceName(),
            ],
        ]);

        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $directive          = new class($container, $config, $factory, $manipulatorFactory) extends Directive {
            #[Override]
            public function name(): string {
                return 'stream';
            }

            /**
             * @inheritDoc
             */
            #[Override]
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
        $this->setConfig([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(Car::class))->getNamespaceName(),
            ],
        ]);

        $root               = null;
        $args               = [];
        $info               = Mockery::mock(ResolveInfo::class);
        $context            = Mockery::mock(GraphQLContext::class);
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $namespace          = json_encode(
            (new ReflectionClass(TestObject::class))->getNamespaceName(),
            JSON_THROW_ON_ERROR,
        );
        $directive          = new class($container, $config, $factory, $manipulatorFactory) extends Directive {
            #[Override]
            public function name(): string {
                return 'stream';
            }

            #[Override]
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
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $namespace          = json_encode(__NAMESPACE__, JSON_THROW_ON_ERROR);
        $directive          = new class($container, $config, $factory, $manipulatorFactory) extends Directive {
            #[Override]
            public function name(): string {
                return 'stream';
            }

            /**
             * @inheritDoc
             */
            #[Override]
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

    /**
     * @param Closure(AstManipulator): (ObjectFieldSource|InterfaceFieldSource) $sourceFactory
     */
    #[DataProvider('dataProviderGetArgKey')]
    public function testGetArgKey(
        Exception|string $expected,
        string $schema,
        DirectiveNode $directiveNode,
        Closure $sourceFactory,
    ): void {
        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $manipulator        = $manipulatorFactory->create(Mockery::mock(DocumentAST::class));
        $source             = $sourceFactory($manipulator);
        $field              = $source->getField();
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $directive          = new class($container, $config, $factory, $manipulatorFactory) extends Directive {
            #[Override]
            public function getArgKey(
                AstManipulator $manipulator,
                ObjectFieldSource|InterfaceFieldSource $source,
            ): string {
                return parent::getArgKey($manipulator, $source);
            }
        };

        self::assertInstanceOf(FieldDefinitionNode::class, $field);

        $directive->hydrate($directiveNode, $field);

        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $this->useGraphQLSchema($schema);

        self::assertEquals($expected, $directive->getArgKey($manipulator, $source));
    }

    public function testResolveField(): void {
        $this->setConfig([
            'lighthouse.namespaces.models' => [
                (new ReflectionClass(Car::class))->getNamespaceName(),
            ],
        ]);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
                field: [Car] @stream(limit: 10)
            }

            type Car {
                carKey: ID!
            }
            GRAPHQL,
        );

        $type  = $this->getGraphQLSchema()->getQueryType();
        $field = $type?->findField('field');

        self::assertNotNull($type);
        self::assertNotNull($field);
        self::assertNotNull($field->astNode);

        $info                  = Mockery::mock(ResolveInfo::class);
        $info->path            = ['field'];
        $info->parentType      = $type;
        $info->fieldDefinition = $field;
        $info->argumentSet     = $this->app()->make(ArgumentSetFactory::class)->fromResolveInfo([], $info);
        $info
            ->shouldReceive('enhanceBuilder')
            ->never();

        $root               = 123;
        $args               = $info->argumentSet->toArray();
        $value              = Mockery::mock(FieldValue::class);
        $context            = Mockery::mock(GraphQLContext::class);
        $builder            = Mockery::mock(EloquentBuilder::class);
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = $this->app()->make(StreamFactory::class);
        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $directive          = Mockery::mock(Directive::class, [$container, $config, $factory, $manipulatorFactory]);
        $directive->shouldAllowMockingProtectedMethods();
        $directive->makePartial();

        $directive->hydrate(
            Parser::directive('@stream'),
            $field->astNode,
        );

        $directive
            ->shouldReceive('getResolver')
            ->once()
            ->andReturn(
                static fn () => $builder,
            );

        $stream = $directive->resolveField($value)($root, $args, $context, $info)->stream;
        $helper = new class() extends Stream {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getItems(): iterable {
                return [];
            }

            #[Override]
            public function getLength(): ?int {
                return null;
            }

            #[Override]
            public function getNextOffset(): ?StreamOffset {
                return null;
            }

            #[Override]
            public function getPreviousOffset(): ?StreamOffset {
                return null;
            }

            /**
             * @param Stream<EloquentBuilder<EloquentModel>|QueryBuilder|ScoutBuilder> $stream
             */
            public function getInternalBuilder(Stream $stream): object {
                return $stream->builder;
            }

            /**
             * @param Stream<EloquentBuilder<EloquentModel>|QueryBuilder|ScoutBuilder> $stream
             */
            public function getInternalKey(Stream $stream): string {
                return $stream->key;
            }

            /**
             * @param Stream<EloquentBuilder<EloquentModel>|QueryBuilder|ScoutBuilder> $stream
             */
            public function getInternalOffset(Stream $stream): StreamOffset {
                return $stream->offset;
            }

            /**
             * @param Stream<EloquentBuilder<EloquentModel>|QueryBuilder|ScoutBuilder> $stream
             */
            public function getInternalLimit(Stream $stream): int {
                return $stream->limit;
            }
        };

        self::assertInstanceOf(Stream::class, $stream);
        self::assertEquals('carKey', $helper->getInternalKey($stream));
        self::assertEquals(10, $helper->getInternalLimit($stream));
        self::assertSame($builder, $helper->getInternalBuilder($stream));
        self::assertEquals(
            new StreamOffset('field', 0, null),
            $helper->getInternalOffset($stream),
        );
    }

    public function testResolveFieldBuilderInvalid(): void {
        $root                  = 123;
        $args                  = ['a' => 'a'];
        $info                  = Mockery::mock(ResolveInfo::class);
        $info->parentType      = new ObjectType(['name' => 'Object', 'fields' => []]);
        $info->fieldDefinition = new FieldDefinition(['name' => 'field', 'type' => Type::string()]);
        $value                 = Mockery::mock(FieldValue::class);
        $context               = Mockery::mock(GraphQLContext::class);
        $config                = $this->app()->make(ConfigResolver::class);
        $factory               = Mockery::mock(StreamFactory::class);
        $manipulatorFactory    = $this->app()->make(ManipulatorFactory::class);
        $container             = $this->app()->make(ContainerResolver::class);
        $directive             = Mockery::mock(Directive::class, [$container, $config, $factory, $manipulatorFactory]);
        $directive->shouldAllowMockingProtectedMethods();
        $directive->makePartial();

        $directive->hydrate(
            Parser::directive('@stream'),
            Parser::fieldDefinition('field: String'),
        );

        $directive
            ->shouldReceive('getFieldValue')
            ->once()
            ->andReturn(
                Mockery::mock(StreamOffset::class),
            );
        $directive
            ->shouldReceive('getResolver')
            ->once()
            ->andReturn(null);

        self::expectException(BuilderInvalid::class);
        self::expectExceptionMessage(
            'The builder must be an object instance, `NULL` given (`type Object { field }`).',
        );

        $directive->resolveField($value)($root, $args, $context, $info);
    }

    public function testResolveFieldBuilderUnsupported(): void {
        $root                  = 123;
        $args                  = ['a' => 'a'];
        $info                  = Mockery::mock(ResolveInfo::class);
        $info->parentType      = new ObjectType(['name' => 'Object', 'fields' => []]);
        $info->fieldDefinition = new FieldDefinition(['name' => 'field', 'type' => Type::string()]);
        $value                 = Mockery::mock(FieldValue::class);
        $context               = Mockery::mock(GraphQLContext::class);
        $factory               = Mockery::mock(StreamFactory::class)->makePartial();
        $manipulatorFactory    = $this->app()->make(ManipulatorFactory::class);
        $config                = $this->app()->make(ConfigResolver::class);
        $container             = $this->app()->make(ContainerResolver::class);
        $directive             = Mockery::mock(Directive::class, [$container, $config, $factory, $manipulatorFactory]);
        $directive->shouldAllowMockingProtectedMethods();
        $directive->makePartial();

        $directive->hydrate(
            Parser::directive('@stream'),
            Parser::fieldDefinition('field: String'),
        );

        $directive
            ->shouldReceive('getFieldValue')
            ->with(StreamOffsetDirective::class, Mockery::andAnyOtherArgs())
            ->once()
            ->andReturn(
                Mockery::mock(StreamOffset::class),
            );
        $directive
            ->shouldReceive('getResolver')
            ->once()
            ->andReturn(
                static fn () => new stdClass(),
            );
        $directive
            ->shouldReceive('getBuilder')
            ->once()
            ->andReturnUsing(
                static fn (object $builder) => $builder,
            );
        $factory
            ->shouldReceive('isSupported')
            ->once()
            ->andReturn(false);

        self::expectException(BuilderUnsupported::class);
        self::expectExceptionMessage(
            'The `stdClass` builder is not supported (`type Object { field }`).',
        );

        $directive->resolveField($value)($root, $args, $context, $info);
    }

    public function testGetFieldValue(): void {
        // Prepare
        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $manipulator        = $manipulatorFactory->create(Mockery::mock(DocumentAST::class));
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $directive          = new class($container, $config, $factory, $manipulatorFactory) extends Directive {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getFieldValue(
                string $directive,
                AstManipulator $manipulator,
                ObjectFieldSource $source,
                ResolveInfo $info,
                array $args,
            ): mixed {
                return parent::getFieldValue(
                    $directive,
                    $manipulator,
                    $source,
                    $info,
                    $args,
                );
            }
        };
        $markerA            = new class() extends DirectiveTest_Directive {
            // empty
        };
        $markerB            = new class() extends DirectiveTest_Directive {
            #[Override]
            public function getFieldArgumentValue(ResolveInfo $info, mixed $value): mixed {
                return parent::getFieldArgumentValue($info, $value) ?? 'default-b';
            }
        };
        $object             = new ObjectType(['name' => 'Object', 'fields' => []]);
        $info               = Mockery::mock(ResolveInfo::class);
        $args               = [
            'a' => null,
            'b' => 'b',
        ];

        $this->app()->make(DirectiveLocator::class)
            ->setResolved('markerA', $markerA::class)
            ->setResolved('markerB', $markerB::class);
        $this->app()->make(TypeRegistry::class)
            ->register($object);

        // Arg
        $value = $directive->getFieldValue(
            DirectiveTest_Directive::class,
            $manipulator,
            (new ObjectSource($manipulator, $object))->getField(
                Parser::fieldDefinition(
                    'test(d: Int @markerA, b: String @markerA @deprecated): String',
                ),
            ),
            $info,
            $args,
        );

        self::assertEquals($args['b'], $value);

        // No Arg
        $value = $directive->getFieldValue(
            DirectiveTest_Directive::class,
            $manipulator,
            (new ObjectSource($manipulator, $object))->getField(
                Parser::fieldDefinition(
                    'test(a: String @markerA @deprecated, b: Int @markerB): String',
                ),
            ),
            $info,
            [],
        );

        self::assertEquals('default-b', $value);
    }

    public function testGetFieldValueNoAttribute(): void {
        self::expectException(ArgumentMissed::class);
        self::expectExceptionMessageMatches(
            '/The `type Object { test }` must have at least one argument marked by `[^`]+` directive./',
        );

        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $manipulator        = $manipulatorFactory->create(Mockery::mock(DocumentAST::class));
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $directive          = new class($container, $config, $factory, $manipulatorFactory) extends Directive {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getFieldValue(
                string $directive,
                AstManipulator $manipulator,
                ObjectFieldSource $source,
                ResolveInfo $info,
                array $args,
            ): mixed {
                return parent::getFieldValue(
                    $directive,
                    $manipulator,
                    $source,
                    $info,
                    $args,
                );
            }
        };
        $object             = new ObjectType(['name' => 'Object', 'fields' => []]);
        $info               = Mockery::mock(ResolveInfo::class);
        $args               = [
            'a' => null,
            'b' => 'b',
            'c' => 123,
        ];

        $this->app()->make(TypeRegistry::class)
            ->register($object);

        $directive->getFieldValue(
            DirectiveTest_Directive::class,
            $manipulator,
            (new ObjectSource($manipulator, $object))->getField(
                Parser::fieldDefinition(
                    'test(a: Int, b: String): String',
                ),
            ),
            $info,
            $args,
        );
    }

    public function testGetFieldValueMultipleAttributes(): void {
        self::expectException(ArgumentsMutuallyExclusive::class);
        self::expectExceptionMessage('The arguments `a`, `b` of `type Object { test }` are mutually exclusive.');

        $manipulatorFactory = $this->app()->make(ManipulatorFactory::class);
        $manipulator        = $manipulatorFactory->create(Mockery::mock(DocumentAST::class));
        $config             = $this->app()->make(ConfigResolver::class);
        $factory            = Mockery::mock(StreamFactory::class);
        $container          = $this->app()->make(ContainerResolver::class);
        $directive          = new class($container, $config, $factory, $manipulatorFactory) extends Directive {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getFieldValue(
                string $directive,
                AstManipulator $manipulator,
                ObjectFieldSource $source,
                ResolveInfo $info,
                array $args,
            ): mixed {
                return parent::getFieldValue(
                    $directive,
                    $manipulator,
                    $source,
                    $info,
                    $args,
                );
            }
        };
        $marker             = Mockery::mock(DirectiveContract::class, FieldArgumentDirective::class);
        $object             = new ObjectType(['name' => 'Object', 'fields' => []]);
        $info               = Mockery::mock(ResolveInfo::class);
        $args               = [
            'a' => null,
            'b' => 'b',
            'c' => 123,
        ];

        $this->app()->make(DirectiveLocator::class)
            ->setResolved('marker', $marker::class);
        $this->app()->make(TypeRegistry::class)
            ->register($object);

        $directive->getFieldValue(
            $marker::class,
            $manipulator,
            (new ObjectSource($manipulator, $object))->getField(
                Parser::fieldDefinition(
                    'test(a: Int @marker, b: String @marker @deprecated): String',
                ),
            ),
            $info,
            $args,
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{
     *      Exception|array<string,mixed>|string,
     *      Closure(static): void,
     *      array<string, mixed>|null,
     *      array<int, mixed>|null,
     *      int,
     *      string|int|null,
     *      }>
     */
    public static function dataProviderDirective(): array {
        return [
            'invalid limit: too low'         => [
                'Validation failed for the field [test].',
                static function (): void {
                    // empty
                },
                null,
                null,
                -123,
                null,
            ],
            'invalid limit: too big'         => [
                'Validation failed for the field [test].',
                static function (): void {
                    // empty
                },
                null,
                null,
                12_345,
                null,
            ],
            'invalid offset: negative value' => [
                'Variable "$offset" got invalid value -1; The offset must be greater or equal to 0.',
                static function (): void {
                    // empty
                },
                null,
                null,
                25,
                -1,
            ],
            'invalid cursor: not a cursor'   => [
                'Variable "$offset" got invalid value "not a cursor"; The cursor is not valid.',
                static function (): void {
                    // empty
                },
                null,
                null,
                25,
                'not a cursor',
            ],
            'first page'                     => [
                [
                    'items'      => [
                        [
                            'id'    => '2dd0bb15-6df9-4490-8b95-4af55f6e0c7a',
                            'value' => 'b',
                        ],
                        [
                            'id'    => '3254df8a-c3ad-4a52-b664-d24807402d76',
                            'value' => 'c',
                        ],
                    ],
                    'length'     => 3,
                    'navigation' => [
                        'previous' => null,
                        'current'  => '{"path":"test","offset":0,"cursor":null}',
                        'next'     => '{"path":"test","offset":2,"cursor":null}',
                    ],
                ],
                static function (): void {
                    TestObject::factory()->create([
                        'id'    => '99187829-9c6c-4f4f-a206-54dc8a552165',
                        'value' => 'a',
                    ]);
                    TestObject::factory()->create([
                        'id'    => '2dd0bb15-6df9-4490-8b95-4af55f6e0c7a',
                        'value' => 'b',
                    ]);
                    TestObject::factory()->create([
                        'id'    => '3254df8a-c3ad-4a52-b664-d24807402d76',
                        'value' => 'c',
                    ]);
                },
                null,
                null,
                2,
                null,
            ],
            'second page: cursor'            => [
                [
                    'items'      => [
                        [
                            'id'    => '99187829-9c6c-4f4f-a206-54dc8a552165',
                            'value' => 'a',
                        ],
                    ],
                    'length'     => 3,
                    'navigation' => [
                        'previous' => '{"path":"test","offset":0,"cursor":null}',
                        'current'  => '{"path":"test","offset":2,"cursor":null}',
                        'next'     => null,
                    ],
                ],
                static function (): void {
                    TestObject::factory()->create([
                        'id'    => '99187829-9c6c-4f4f-a206-54dc8a552165',
                        'value' => 'a',
                    ]);
                    TestObject::factory()->create([
                        'id'    => '2dd0bb15-6df9-4490-8b95-4af55f6e0c7a',
                        'value' => 'b',
                    ]);
                    TestObject::factory()->create([
                        'id'    => '3254df8a-c3ad-4a52-b664-d24807402d76',
                        'value' => 'c',
                    ]);
                },
                null,
                null,
                2,
                '{"path":"test","cursor":null,"offset":2}',
            ],
            'second page: offset'            => [
                [
                    'items'      => [
                        [
                            'id'    => '99187829-9c6c-4f4f-a206-54dc8a552165',
                            'value' => 'a',
                        ],
                    ],
                    'length'     => 3,
                    'navigation' => [
                        'previous' => '{"path":"test","offset":0,"cursor":null}',
                        'current'  => '{"path":"test","offset":2,"cursor":null}',
                        'next'     => null,
                    ],
                ],
                static function (): void {
                    TestObject::factory()->create([
                        'id'    => '99187829-9c6c-4f4f-a206-54dc8a552165',
                        'value' => 'a',
                    ]);
                    TestObject::factory()->create([
                        'id'    => '2dd0bb15-6df9-4490-8b95-4af55f6e0c7a',
                        'value' => 'b',
                    ]);
                    TestObject::factory()->create([
                        'id'    => '3254df8a-c3ad-4a52-b664-d24807402d76',
                        'value' => 'c',
                    ]);
                },
                null,
                null,
                2,
                2,
            ],
            'search'                         => [
                [
                    'items'      => [
                        [
                            'id'    => '6aea881f-2b50-4295-ac4f-afed3430e6cd',
                            'value' => 'b',
                        ],
                    ],
                    'length'     => 1,
                    'navigation' => [
                        'previous' => null,
                        'current'  => '{"path":"test","offset":0,"cursor":null}',
                        'next'     => null,
                    ],
                ],
                static function (): void {
                    TestObject::factory()->create([
                        'id'    => '19d56286-0bb6-4b3a-a808-896a157b1f0f',
                        'value' => 'a',
                    ]);
                    TestObject::factory()->create([
                        'id'    => '6aea881f-2b50-4295-ac4f-afed3430e6cd',
                        'value' => 'b',
                    ]);
                },
                [
                    'field' => [
                        'value' => [
                            'equal' => 'b',
                        ],
                    ],
                ],
                null,
                25,
                null,
            ],
            'sort'                           => [
                [
                    'items'      => [
                        [
                            'id'    => '8f1a92ce-2da3-4119-8a87-1395d86fe4eb',
                            'value' => 'a',
                        ],
                        [
                            'id'    => 'b7af5747-5ac7-437e-8f6f-341c4df17aea',
                            'value' => 'b',
                        ],
                        [
                            'id'    => 'dea39eea-c033-4ce0-bbdd-ac22afe25bc5',
                            'value' => 'b',
                        ],
                    ],
                    'length'     => 3,
                    'navigation' => [
                        'previous' => null,
                        'current'  => '{"path":"test","offset":0,"cursor":null}',
                        'next'     => null,
                    ],
                ],
                static function (): void {
                    TestObject::factory()->create([
                        'id'    => 'dea39eea-c033-4ce0-bbdd-ac22afe25bc5',
                        'value' => 'b',
                    ]);
                    TestObject::factory()->create([
                        'id'    => '8f1a92ce-2da3-4119-8a87-1395d86fe4eb',
                        'value' => 'a',
                    ]);
                    TestObject::factory()->create([
                        'id'    => 'b7af5747-5ac7-437e-8f6f-341c4df17aea',
                        'value' => 'b',
                    ]);
                },
                null,
                [
                    [
                        'field' => ['value' => 'asc'],
                    ],
                ],
                25,
                null,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      Exception|BuilderInfo|null,
     *      Closure(AstManipulator): (ObjectFieldSource|InterfaceFieldSource),
     *      Closure():mixed|array{string, string}|null
     *      }>
     */
    public static function dataProviderGetBuilderInfo(): array {
        $class   = new DirectiveTest_Model();
        $factory = static function (AstManipulator $manipulator): ObjectFieldSource {
            return (new ObjectSource($manipulator, new ObjectType(['name' => 'ObjectA', 'fields' => []])))
                ->getField(
                    Parser::fieldDefinition('test: String'),
                );
        };

        return [
            'null'                        => [
                null,
                $factory,
                null,
            ],
            'Closure(): mixed'            => [
                null,
                $factory,
                static function (): mixed {
                    return null;
                },
            ],
            'Closure(): class'            => [
                BuilderInfo::create(EloquentBuilder::class),
                $factory,
                static function (): EloquentBuilder {
                    throw new Exception('Should not be called.');
                },
            ],
            'Closure(): union'            => [
                null,
                $factory,
                static function () use ($class): stdClass|self {
                    return $class->union();
                },
            ],
            'array(Unknown, method)'      => [
                null,
                $factory,
                ['Unknown', 'method'],
            ],
            'array(Class, unknownMethod)' => [
                null,
                $factory,
                [stdClass::class, 'unknownMethod'],
            ],
            'array(Class, method: union)' => [
                null,
                $factory,
                [$class::class, 'union'],
            ],
            'array(Class, method)'        => [
                BuilderInfo::create(EloquentBuilder::class),
                $factory,
                [$class::class, 'method'],
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      Exception|BuilderInfo|null,
     *      Closure(AstManipulator): (ObjectFieldSource|InterfaceFieldSource),
     *      Closure():mixed|array{string, string}|null
     *      }>
     */
    public static function dataProviderGetBuilderInfoScoutBuilder(): array {
        $factory = static function (AstManipulator $manipulator): ObjectFieldSource {
            return (new ObjectSource($manipulator, new ObjectType(['name' => 'ObjectA', 'fields' => []])))
                ->getField(
                    Parser::fieldDefinition('test(search: String! @search): String'),
                );
        };

        return [
            '@search/Eloquent: array(Class, method)' => [
                BuilderInfo::create(ScoutBuilder::class),
                $factory,
                [DirectiveTest_Model::class, 'method'],
            ],
            '@search: Closure(): class'              => [
                null,
                $factory,
                static function (): QueryBuilder {
                    throw new Exception('Should not be called.');
                },
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
            'multiple' => [
                null,
                '{relation: "engine", model: "ModelClass"}',
            ],
        ];
    }

    /**
     * @return array<string, array{
     *      Exception|string,
     *      string,
     *      DirectiveNode,
     *      Closure(AstManipulator): (ObjectFieldSource|InterfaceFieldSource)
     *      }>
     */
    public static function dataProviderGetArgKey(): array {
        $schema  = <<<'GRAPHQL'
            type ObjectA {
                id: ID!
            }

            type ObjectB {
                id: ID
            }

            type ObjectC {
                id: ID! @rename(attribute: "renamed")
            }
            GRAPHQL;
        $factory = static function (AstManipulator $manipulator): ObjectFieldSource {
            return (new ObjectSource($manipulator, new ObjectType(['name' => 'ObjectA', 'fields' => []])))
                ->getField(
                    Parser::fieldDefinition('test: String'),
                );
        };

        return [
            'Explicit'           => [
                'explicitKey',
                $schema,
                Parser::directive('@stream(key: "explicitKey")'),
                $factory,
            ],
            'Explicit (invalid)' => [
                new KeyUnknown('type ObjectA { test }'),
                $schema,
                Parser::directive('@stream(key: "")'),
                $factory,
            ],
            'Implicit'           => [
                'id',
                $schema,
                Parser::directive('@stream'),
                static function (AstManipulator $manipulator): ObjectFieldSource {
                    return (new ObjectSource($manipulator, new ObjectType(['name' => 'Object', 'fields' => []])))
                        ->getField(
                            Parser::fieldDefinition('test: ObjectA'),
                        );
                },
            ],
            'Invalid type'       => [
                new KeyUnknown('type ObjectA { test }'),
                $schema,
                Parser::directive('@stream'),
                static function (AstManipulator $manipulator): ObjectFieldSource {
                    return (new ObjectSource($manipulator, new ObjectType(['name' => 'ObjectA', 'fields' => []])))
                        ->getField(
                            Parser::fieldDefinition('test: ObjectB'),
                        );
                },
            ],
            'Converted'          => [
                'id',
                $schema,
                Parser::directive('@stream'),
                static function (AstManipulator $manipulator): ObjectFieldSource {
                    return (new ObjectSource($manipulator, new ObjectType(['name' => 'ObjectB', 'fields' => []])))
                        ->getField(
                            Parser::fieldDefinition('test: ObjectAsStream'),
                        );
                },
            ],
            '@rename'            => [
                'renamed',
                $schema,
                Parser::directive('@stream'),
                static function (AstManipulator $manipulator): ObjectFieldSource {
                    return (new ObjectSource($manipulator, new ObjectType(['name' => 'ObjectB', 'fields' => []])))
                        ->getField(
                            Parser::fieldDefinition('test: ObjectC'),
                        );
                },
            ],
        ];
    }
    //</editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 * @implements FieldArgumentDirective<mixed>
 */
abstract class DirectiveTest_Directive implements DirectiveContract, FieldArgumentDirective {
    #[Override]
    public static function definition(): string {
        throw new Exception('Should not be called.');
    }

    #[Override]
    public function getFieldArgumentValue(ResolveInfo $info, mixed $value): mixed {
        return $value;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class DirectiveTest_Encrypter implements StringEncrypter {
    #[Override]
    public function encryptString(mixed $value): string {
        return $value;
    }

    #[Override]
    public function decryptString(mixed $payload): string {
        return $payload;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class DirectiveTest_Model {
    /**
     * @return EloquentBuilder<EloquentModel>
     */
    public function method(): EloquentBuilder {
        throw new Exception('Should not be called.');
    }

    public function union(): stdClass|DirectiveTest {
        throw new Exception('Should not be called.');
    }
}
