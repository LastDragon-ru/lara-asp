<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ObjectFieldNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\StringValueNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\PropertyBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 * @extends ListBlock<PropertyBlock<Value>>
 */
#[GraphQLAstNode(ObjectValueNode::class)]
#[GraphQLAstNode(ObjectFieldNode::class)]
class ObjectValue extends ListBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        ObjectValueNode $definition,
    ) {
        parent::__construct($context, $level, $used);

        foreach ($definition->fields as $field) {
            $name        = $field->name->value;
            $this[$name] = new PropertyBlock(
                $context,
                $name,
                new Value(
                    $context,
                    $level + 1 + (int) ($field->value instanceof StringValueNode),
                    $used,
                    $field->value,
                ),
            );
        }
    }

    protected function getPrefix(): string {
        return '{';
    }

    protected function getSuffix(): string {
        return '}';
    }

    protected function getEmptyValue(): string {
        return "{$this->getPrefix()}{$this->getSuffix()}";
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeArguments();
    }
}
