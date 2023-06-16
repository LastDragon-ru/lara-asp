<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ScalarTypeExtensionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 *
 * @extends DefinitionBlock<ScalarTypeExtensionNode>
 */
#[GraphQLAstNode(ScalarTypeExtensionNode::class)]
class ScalarTypeExtension extends DefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        ScalarTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'extend scalar';
    }

    protected function body(int $used): Block|string|null {
        return null;
    }

    protected function fields(int $used): Block|string|null {
        return null;
    }
}
