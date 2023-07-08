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
        SchemaExtensionNode $definition,
    ) {
        parent::__construct($context, $definition);
    }

    protected function prefix(): ?string {
        return 'extend schema';
    }

    protected function fields(int $level, int $used, bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $operations = [];

        foreach ($definition->operationTypes as $operation) {
            $operations[$operation->operation] = $operation->type;
        }

        return new RootOperationTypesDefinition(
            $this->getContext(),
            $operations,
        );
    }
}
