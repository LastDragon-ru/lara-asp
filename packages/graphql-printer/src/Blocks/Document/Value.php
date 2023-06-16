<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Language\AST\ObjectFieldNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\AST\VariableNode;
use GraphQL\Language\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\PropertyBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\StringBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 */
#[GraphQLAstNode(VariableNode::class)]
#[GraphQLAstNode(NullValueNode::class)]
#[GraphQLAstNode(IntValueNode::class)]
#[GraphQLAstNode(FloatValueNode::class)]
#[GraphQLAstNode(StringValueNode::class)]
#[GraphQLAstNode(BooleanValueNode::class)]
#[GraphQLAstNode(EnumValueNode::class)]
#[GraphQLAstNode(ListValueNode::class)]
#[GraphQLAstNode(ObjectValueNode::class)]
#[GraphQLAstNode(ObjectFieldNode::class)]
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
            $content = new ListValue($context, $level, $used);

            foreach ($this->node->values as $value) {
                $content[] = new self($context, $level + 1, $used, $value);
            }
        } elseif ($this->node instanceof ObjectValueNode) {
            $content = new ObjectValue($context, $level, $used);

            foreach ($this->node->fields as $field) {
                $name           = $field->name->value;
                $content[$name] = new PropertyBlock(
                    $context,
                    $name,
                    new self(
                        $context,
                        $level + 1 + (int) ($field->value instanceof StringValueNode),
                        $used,
                        $field->value,
                    ),
                );
            }
        } elseif ($this->node instanceof StringValueNode) {
            $content = $this->node->block
                ? new StringBlock($context, $level, 0, $this->node->value)
                : Printer::doPrint($this->node);
        } else {
            $content = Printer::doPrint($this->node);
        }

        return (string) $this->addUsed($content);
    }
}
