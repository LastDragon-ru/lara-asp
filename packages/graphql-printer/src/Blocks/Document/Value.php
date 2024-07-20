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
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use Override;

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
    public function __construct(
        Context $context,
        private ValueNode&Node $node,
        private (TypeNode&Node)|Type|null $type,
    ) {
        parent::__construct($context);
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        // Content
        $context = $this->getContext();
        $content = match (true) {
            $this->node instanceof ListValueNode
                => new ListValue($context, $this->node, $this->type),
            $this->node instanceof ObjectValueNode
                => new ObjectValue($context, $this->node, $this->type),
            $this->node instanceof StringValueNode && $this->node->block
                => new StringValue($context, $this->node->value),
            $this->node instanceof NullValueNode
                => 'null',
            $this->node instanceof IntValueNode,
            $this->node instanceof FloatValueNode,
            $this->node instanceof EnumValueNode
                => $this->node->value,
            $this->node instanceof VariableNode
                => new VariableValue($context, $this->node),
            property_exists($this->node, 'value')
                => json_encode($this->node->value, JSON_THROW_ON_ERROR),
            default
                => throw new Unsupported($this->node),
        };

        // Statistics
        $collector->addUsed($content);

        if ($this->type) {
            $collector->addUsedType($this->getTypeName($this->type));
        }

        // Return
        if ($content instanceof Block) {
            $content = $content->serialize($collector, $level, $used);
        }

        return $content;
    }
}
