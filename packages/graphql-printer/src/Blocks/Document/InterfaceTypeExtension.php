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
        int $level,
        int $used,
        InterfaceTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'extend interface';
    }
}
