<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectFieldNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 * @extends ListBlock<ObjectField, array-key, ObjectFieldNode>
 */
class ObjectValue extends ListBlock {
    /**
     * @param (TypeNode&Node)|Type|null $type
     */
    public function __construct(
        Context $context,
        ObjectValueNode $definition,
        private TypeNode|Type|null $type,
    ) {
        parent::__construct($context, $definition->fields);
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
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineArguments();
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeArguments();
    }

    protected function block(string|int $key, mixed $item): Block {
        $name = $item->name->value;
        $type = $this->type
            ? $this->getContext()->getField($this->type, $name)?->getType()
            : null;

        return new ObjectField(
            $this->getContext(),
            $item,
            $type,
        );
    }
}
