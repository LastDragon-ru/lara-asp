<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ObjectDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends ObjectDefinitionBlock<InterfaceTypeExtensionNode>
 */
#[GraphQLAstNode(InterfaceTypeExtensionNode::class)]
class InterfaceTypeExtension extends ObjectDefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        InterfaceTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'extend interface';
    }
}
