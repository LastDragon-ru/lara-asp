<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\EnumTypeExtensionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\EnumDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 *
 * @extends EnumDefinitionBlock<EnumTypeExtensionNode>
 */
#[GraphQLAstNode(EnumTypeExtensionNode::class)]
class EnumTypeExtension extends EnumDefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        EnumTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function prefix(): string {
        return 'extend enum';
    }
}
