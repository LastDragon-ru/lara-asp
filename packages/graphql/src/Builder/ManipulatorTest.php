<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Nuwave\Lighthouse\Pagination\PaginationServiceProvider;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_merge;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator
 */
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

        $schema = $this->getGraphQLSchema($graphql);
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
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{?string,string}>
     */
    public function dataProviderGetPlaceholderTypeDefinitionNode(): array {
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
