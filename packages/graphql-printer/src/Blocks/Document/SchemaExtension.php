<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\SchemaExtensionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 *
 * @extends DefinitionBlock<SchemaExtensionNode>
 */
#[GraphQLAstNode(SchemaExtensionNode::class)]
class SchemaExtension extends DefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        SchemaExtensionNode $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function prefix(): ?string {
        return 'extend schema';
    }

    protected function fields(int $used, bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $fields     = new RootOperationTypesDefinition(
            $this->getContext(),
            $this->getLevel(),
            $used,
        );

        foreach ($definition->operationTypes as $operation) {
            $fields[] = new RootOperationTypeDefinition(
                $this->getContext(),
                $this->getLevel() + 1,
                $this->getUsed(),
                $operation->operation,
                $operation->type,
            );
        }

        return $fields;
    }
}
