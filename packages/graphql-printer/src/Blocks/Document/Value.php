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
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\AST\ValueNode;
use GraphQL\Language\AST\VariableNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values\ListValue;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values\ObjectValue;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values\StringValue;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values\VariableValue;
use LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions\Unsupported;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

use function json_encode;
use function property_exists;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
#[GraphQLAstNode(VariableNode::class)]
#[GraphQLAstNode(ObjectValueNode::class)]
#[GraphQLAstNode(ObjectFieldNode::class)]
#[GraphQLAstNode(ListValueNode::class)]
#[GraphQLAstNode(NullValueNode::class)]
#[GraphQLAstNode(IntValueNode::class)]
#[GraphQLAstNode(FloatValueNode::class)]
#[GraphQLAstNode(StringValueNode::class)]
#[GraphQLAstNode(BooleanValueNode::class)]
#[GraphQLAstNode(EnumValueNode::class)]
class Value extends Block {
    /**
     * @param ValueNode&Node            $node
     * @param (TypeNode&Node)|Type|null $type
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        protected ValueNode $node,
        protected TypeNode|Type|null $type = null,
    ) {
        parent::__construct($context, $level, $used);
    }

    protected function content(): string {
        // Content
        $context = $this->getContext();
        $level   = $this->getLevel();
        $used    = $this->getUsed();
        $content = match (true) {
            $this->node instanceof ListValueNode
                => new ListValue($context, $level, $used, $this->node),
            $this->node instanceof ObjectValueNode
                => new ObjectValue($context, $level, $used, $this->node, $this->type),
            $this->node instanceof StringValueNode && $this->node->block
                => new StringValue($context, $level, 0, $this->node->value),
            $this->node instanceof NullValueNode
                => 'null',
            $this->node instanceof IntValueNode,
            $this->node instanceof FloatValueNode,
            $this->node instanceof EnumValueNode
                => $this->node->value,
            $this->node instanceof VariableNode
                => new VariableValue($context, $level, 0, $this->node),
            property_exists($this->node, 'value')
                => json_encode($this->node->value, JSON_THROW_ON_ERROR),
            default
                => throw new Unsupported($this->node),
        };

        // Statistics
        if ($this->type) {
            $this->addUsedType($this->getTypeName($this->type));
        }

        // Return
        return (string) $this->addUsed($content);
    }
}
