<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\InputObjectDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 *
 * @extends InputObjectDefinitionBlock<InputObjectTypeExtensionNode>
 */
#[GraphQLAstNode(InputObjectTypeExtensionNode::class)]
class InputObjectTypeExtension extends InputObjectDefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        InputObjectTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function prefix(): ?string {
        return 'extend input';
    }
}
