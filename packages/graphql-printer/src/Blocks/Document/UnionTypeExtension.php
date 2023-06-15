<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\UnionTypeExtensionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\UnionDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 *
 * @extends UnionDefinitionBlock<UnionTypeExtensionNode>
 */
#[GraphQLAstNode(UnionTypeExtensionNode::class)]
class UnionTypeExtension extends UnionDefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        UnionTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'extend union';
    }
}
