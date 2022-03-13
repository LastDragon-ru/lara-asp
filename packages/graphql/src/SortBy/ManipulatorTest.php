<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Pagination\PaginationServiceProvider;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_merge;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Manipulator
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
     * @covers ::getPlaceholderTypeDefinitionNode
     *
     * @dataProvider dataProviderGetPlaceholderTypeDefinitionNode
     */
    public function testGetPlaceholderTypeDefinitionNode(?string $expected, string $graphql): void {
        $locator     = $this->app->make(DirectiveLocator::class);
        $registry    = $this->app->make(TypeRegistry::class);
        $manipulator = new class($locator, $registry) extends Manipulator {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct(
                protected DirectiveLocator $directives,
                protected TypeRegistry $types,
            ) {
                // empty
            }

            public function getPlaceholderTypeDefinitionNode(FieldDefinitionNode $field): TypeDefinitionNode|Type|null {
                return parent::getPlaceholderTypeDefinitionNode($field);
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
            self::assertEquals($expected, $type->name);
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
