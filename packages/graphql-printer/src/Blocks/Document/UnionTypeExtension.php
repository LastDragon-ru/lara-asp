<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\UnionTypeExtensionNode;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\UnionDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends UnionDefinitionBlock<UnionTypeExtensionNode>
 */
#[GraphQLAstNode(UnionTypeExtensionNode::class)]
class UnionTypeExtension extends UnionDefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        UnionTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'extend union';
    }
}
