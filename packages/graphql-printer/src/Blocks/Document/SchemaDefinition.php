<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\OperationTypeDefinitionNode;
use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

use function array_fill_keys;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function count;

use const ARRAY_FILTER_USE_BOTH;

/**
 * @internal
 *
 * @extends DefinitionBlock<SchemaDefinitionNode|Schema>
 */
#[GraphQLAstNode(SchemaDefinitionNode::class)]
#[GraphQLAstNode(OperationTypeDefinitionNode::class)]
#[GraphQLDefinition(Schema::class)]
class SchemaDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        SchemaDefinitionNode|Schema $definition,
    ) {
        parent::__construct($context, $definition);
    }

    protected function prefix(): ?string {
        return 'schema';
    }

    protected function content(Collector $collector, int $level, int $used): string {
        $content = parent::content($collector, $level, $used);

        if ($this->isUseDefaultRootOperationTypeNames()) {
            $content = '';
        }

        return $content;
    }

    protected function fields(int $level, int $used, bool $multiline): ?Block {
        return new RootOperationTypesDefinition(
            $this->getContext(),
            $this->getOperationsTypes(),
        );
    }

    private function isUseDefaultRootOperationTypeNames(): bool {
        // Directives?
        if (count($this->getDefinitionDirectives()) > 0) {
            return false;
        }

        // Names?
        $default     = $this->getOperationsDefaultTypes();
        $operations  = $this->getOperationsTypes();
        $nonStandard = array_filter(
            $operations,
            static function (NamedTypeNode|ObjectType $type, string $operation) use ($default): bool {
                $name   = $type instanceof NamedTypeNode
                    ? $type->name->value
                    : $type->name;
                $custom = !isset($default[$operation])
                    || $default[$operation] !== $name;

                return $custom;
            },
            ARRAY_FILTER_USE_BOTH,
        );

        return !$nonStandard;
    }

    /**
     * @return array{
     *      query?: NamedTypeNode|ObjectType,
     *      mutation?: NamedTypeNode|ObjectType,
     *      subscription?: NamedTypeNode|ObjectType,
     *      }
     */
    private function getOperationsTypes(): array {
        $definition = $this->getDefinition();
        $operations = array_fill_keys(
            array_keys($this->getOperationsDefaultTypes()),
            null,
        );

        if ($definition instanceof Schema) {
            foreach ($operations as $operation => $type) {
                $type = $definition->getOperationType($operation);

                if ($type) {
                    $operations[$operation] = $type;
                }
            }
        } else {
            foreach ($definition->operationTypes as $operation) {
                if (array_key_exists($operation->operation, $operations)) {
                    $operations[$operation->operation] = $operation->type;
                }
            }
        }

        return array_filter($operations);
    }

    /**
     * @return array{
     *      query: string,
     *      mutation: string,
     *      subscription: string,
     *      }
     */
    private function getOperationsDefaultTypes(): array {
        return [
            'query'        => 'Query',
            'mutation'     => 'Mutation',
            'subscription' => 'Subscription',
        ];
    }
}
