<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\TypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 *
 * @extends TypeDefinitionBlock<InterfaceTypeExtensionNode>
 */
#[GraphQLAstNode(InterfaceTypeExtensionNode::class)]
class InterfaceTypeExtension extends TypeDefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        InterfaceTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $definition);
    }

    protected function prefix(): ?string {
        return 'extend interface';
    }
}
