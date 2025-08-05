<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\InputObjectDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends InputObjectDefinitionBlock<InputObjectTypeExtensionNode>
 */
#[GraphQLAstNode(InputObjectTypeExtensionNode::class)]
class InputObjectTypeExtension extends InputObjectDefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        InputObjectTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'extend input';
    }
}
