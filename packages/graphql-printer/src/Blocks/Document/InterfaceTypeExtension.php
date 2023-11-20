<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ObjectDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
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
