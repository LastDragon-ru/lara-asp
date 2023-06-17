<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\VariableNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 */
#[GraphQLAstNode(VariableNode::class)]
class Variable extends Block implements NamedBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        protected VariableNode $node,
    ) {
        parent::__construct($context, $level, $used);
    }

    public function getName(): string {
        return $this->node->name->value;
    }

    protected function content(): string {
        return "\${$this->getName()}";
    }
}
