<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\AST\VariableNode;
use GraphQL\Language\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\StringBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 */
#[GraphQLAstNode(IntValueNode::class)]
#[GraphQLAstNode(FloatValueNode::class)]
#[GraphQLAstNode(StringValueNode::class)]
#[GraphQLAstNode(BooleanValueNode::class)]
#[GraphQLAstNode(EnumValueNode::class)]
class Value extends Block {
    /**
     * @param ValueNode&Node $node
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        protected ValueNode $node,
    ) {
        parent::__construct($context, $level, $used);
    }

    protected function content(): string {
        $content = '';
        $context = $this->getContext();
        $level   = $this->getLevel();
        $used    = $this->getUsed();

        if ($this->node instanceof ListValueNode) {
            $content = new ListValue($context, $level, $used, $this->node);
        } elseif ($this->node instanceof ObjectValueNode) {
            $content = new ObjectValue($context, $level, $used, $this->node);
        } elseif ($this->node instanceof StringValueNode) {
            $content = $this->node->block
                ? new StringBlock($context, $level, 0, $this->node->value)
                : Printer::doPrint($this->node);
        } elseif ($this->node instanceof NullValueNode) {
            $content = new NullValue($context, $level, 0, $this->node);
        } elseif ($this->node instanceof VariableNode) {
            $content = new Variable($context, $level, 0, $this->node);
        } else {
            $content = Printer::doPrint($this->node);
        }

        return (string) $this->addUsed($content);
    }
}
