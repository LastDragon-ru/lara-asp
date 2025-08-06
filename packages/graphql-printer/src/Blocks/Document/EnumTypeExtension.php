<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\EnumTypeExtensionNode;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\EnumDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends EnumDefinitionBlock<EnumTypeExtensionNode>
 */
#[GraphQLAstNode(EnumTypeExtensionNode::class)]
class EnumTypeExtension extends EnumDefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        EnumTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): string {
        return 'extend enum';
    }
}
