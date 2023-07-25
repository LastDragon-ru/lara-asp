<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorsDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Pagination\PaginationServiceProvider;
use Nuwave\Lighthouse\Schema\AST\ASTBuilder;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function array_map;
use function array_merge;
use function is_a;

/**
 * @internal
 */
#[CoversClass(Manipulator::class)]
class ManipulatorTest extends TestCase {
    // <editor-fold desc="Prepare">
    // =========================================================================
    /**
     * @inheritDoc
     */
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            PaginationServiceProvider::class,
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderGetPlaceholderTypeDefinitionNode
     */
    public function testGetPlaceholderTypeDefinitionNode(?string $expected, string $graphql): void {
        $ast         = Mockery::mock(DocumentAST::class);
        $types       = $this->app->make(TypeRegistry::class);
        $directives  = $this->app->make(DirectiveLocator::class);
        $manipulator = new class($directives, $types, $ast) extends Manipulator {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct(
                protected DirectiveLocator $directives,
                protected TypeRegistry $types,
                protected DocumentAST $document,
            ) {
                // empty
            }

            protected function getDirectives(): DirectiveLocator {
                return $this->directives;
            }

            public function getDocument(): DocumentAST {
                return $this->document;
            }

            protected function getTypes(): TypeRegistry {
                return $this->types;
            }
        };

        $schema = $this->useGraphQLSchema($graphql)->getCurrentGraphQLSchema();
        $query  = $schema->getType('Query');
        $field  = $query instanceof ObjectType
            ? $query->getField('field')->astNode
            : null;

        self::assertNotNull($field);

        $type = $manipulator->getPlaceholderTypeDefinitionNode($field);

        if ($expected !== null) {
            self::assertNotNull($type);
            self::assertEquals($expected, $manipulator->getNodeName($type));
        } else {
            self::assertNull($type);
        }
    }

    public function testGetTypeOperators(): void {
        // Operators
        $scope     = new class() implements Scope {
            // empty;
        };
        $builder   = new stdClass();
        $aOperator = ManipulatorTest_OperatorA::class;
        $bOperator = ManipulatorTest_OperatorB::class;
        $cOperator = ManipulatorTest_OperatorC::class;

        // Types
        $types = $this->app->make(TypeRegistry::class);

        $types->register(
            new CustomScalarType([
                'name' => 'TestScalar',
            ]),
        );
        $types->register(
            new CustomScalarType([
                'name' => 'TestOperators',
            ]),
        );
        $types->register(
            new CustomScalarType([
                'name' => 'TestBuiltinOperators',
            ]),
        );

        // Directives
        $directives = $this->app->make(DirectiveLocator::class);

        $directives->setResolved('operators', ManipulatorTest_Operators::class);
        $directives->setResolved('aOperator', $aOperator);
        $directives->setResolved('bOperator', $bOperator);
        $directives->setResolved('cOperator', $cOperator);

        // Schema
        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            scalar TestScalar
            @aOperator
            @bOperator
            @cOperator

            scalar TestOperators
            @operators(type: "TestScalar")

            scalar TestBuiltinOperators
            @operators(type: "TestBuiltinOperators")
            @aOperator

            type Query {
                test: Int @all
            }
            GRAPHQL,
        );

        // Operators
        $config    = [];
        $default   = [
            Operators::ID          => [
                $aOperator,
                $bOperator,
            ],
            Operators::Int         => [
                $bOperator,
                $cOperator,
            ],
            'TestBuiltinOperators' => [
                $cOperator,
            ],
        ];
        $operators = new class($config, $default) extends Operators {
            /**
             * @param array<string, array<class-string<Operator>|string>> $operators
             * @param array<string, array<class-string<Operator>|string>> $default
             */
            public function __construct(array $operators = [], array $default = []) {
                parent::__construct($operators);

                $this->default = $default;
            }

            public function getScope(): string {
                return Scope::class;
            }
        };

        // Manipulator
        $document    = $this->app->make(ASTBuilder::class)->documentAST();
        $manipulator = $this->app->make(Manipulator::class, [
            'document'    => $document,
            'builderInfo' => new BuilderInfo($builder::class, $builder::class),
        ]);

        $manipulator->addOperators($operators);

        // Test
        $map = static function (Operator $operator): string {
            return $operator::class;
        };

        self::assertEquals(
            [
                $aOperator,
            ],
            array_map($map, $manipulator->getTypeOperators($operators->getScope(), Operators::ID)),
        );
        self::assertEquals(
            [
                $aOperator,
                $cOperator,
            ],
            array_map($map, $manipulator->getTypeOperators($operators->getScope(), Operators::ID, Operators::Int)),
        );
        self::assertEquals(
            [
                // empty (another scope)
            ],
            array_map($map, $manipulator->getTypeOperators($scope::class, Operators::ID)),
        );
        self::assertEquals(
            [
                $aOperator,
            ],
            array_map($map, $manipulator->getTypeOperators($operators->getScope(), 'TestScalar')),
        );
        self::assertEquals(
            [
                $aOperator,
            ],
            array_map($map, $manipulator->getTypeOperators($operators->getScope(), 'TestOperators')),
        );
        self::assertEquals(
            [
                $cOperator,
                $aOperator,
            ],
            array_map($map, $manipulator->getTypeOperators($operators->getScope(), 'TestBuiltinOperators')),
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{?string,string}>
     */
    public static function dataProviderGetPlaceholderTypeDefinitionNode(): array {
        return [
            'field nullable'              => [
                'Test',
                /** @lang GraphQL */
                <<<'GRAPHQL'
                type Query {
                    field: Test @mock
                }

                type Test {
                    field: Int
                }
                GRAPHQL,
            ],
            'field not null'              => [
                'Test',
                /** @lang GraphQL */
                <<<'GRAPHQL'
                type Query {
                    field: Test! @mock
                }

                type Test {
                    field: Int
                }
                GRAPHQL,
            ],
            'list'                        => [
                'Test',
                /** @lang GraphQL */
                <<<'GRAPHQL'
                type Query {
                    field: [Test] @mock
                }

                type Test {
                    field: Int
                }
                GRAPHQL,
            ],
            '@paginate(type: PAGINATOR)'  => [
                'Test',
                /** @lang GraphQL */
                <<<'GRAPHQL'
                type Query {
                    field: [Test!]
                    @paginate(
                        model: "\\LastDragon_ru\\LaraASP\\GraphQL\\Testing\\Package\\Model"
                        type: PAGINATOR
                    )
                }

                type Test {
                    field: Int
                }
                GRAPHQL,
            ],
            '@paginate(type: SIMPLE)'     => [
                'Test',
                /** @lang GraphQL */
                <<<'GRAPHQL'
                type Query {
                    field: [Test!]
                    @paginate(
                        model: "\\LastDragon_ru\\LaraASP\\GraphQL\\Testing\\Package\\Model"
                        type: SIMPLE
                    )
                }

                type Test {
                    field: Int
                }
                GRAPHQL,
            ],
            '@paginate(type: CONNECTION)' => [
                'Test',
                /** @lang GraphQL */
                <<<'GRAPHQL'
                type Query {
                    field: [Test!]
                    @paginate(
                        model: "\\LastDragon_ru\\LaraASP\\GraphQL\\Testing\\Package\\Model"
                        type: CONNECTION
                    )
                }

                type Test {
                    field: Int
                }
                GRAPHQL,
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
 */
class ManipulatorTest_Operators extends OperatorsDirective implements Scope {
    protected static function getDirectiveName(): string {
        return '@operators';
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ManipulatorTest_OperatorA extends OperatorDirective implements Operator, Scope {
    public static function getDirectiveName(): string {
        return 'aOperator';
    }

    public static function getName(): string {
        return 'a';
    }

    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return $source->getTypeName();
    }

    public function getFieldDescription(): string {
        return '';
    }

    public function isBuilderSupported(string $builder): bool {
        return is_a($builder, stdClass::class, true);
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        return $builder;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ManipulatorTest_OperatorB extends OperatorDirective implements Operator {
    public static function getDirectiveName(): string {
        return 'bOperator';
    }

    public static function getName(): string {
        return 'b';
    }

    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return $source->getTypeName();
    }

    public function getFieldDescription(): string {
        return '';
    }

    public function isBuilderSupported(string $builder): bool {
        return false;
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        return $builder;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ManipulatorTest_OperatorC extends OperatorDirective implements Operator {
    public static function getDirectiveName(): string {
        return 'cOperator';
    }

    public static function getName(): string {
        return 'c';
    }

    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return $source->getTypeName();
    }

    public function getFieldDescription(): string {
        return '';
    }

    public function isBuilderSupported(string $builder): bool {
        return is_a($builder, stdClass::class, true);
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        return $builder;
    }
}
