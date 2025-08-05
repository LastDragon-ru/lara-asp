<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Values;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectFieldNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 * @extends ListBlock<ObjectField, array-key, ObjectFieldNode>
 */
class ObjectValue extends ListBlock {
    public function __construct(
        Context $context,
        ObjectValueNode $definition,
        private (TypeNode&Node)|Type|null $type,
    ) {
        parent::__construct($context, $definition->fields);
    }

    #[Override]
    protected function getPrefix(): string {
        return '{';
    }

    #[Override]
    protected function getSuffix(): string {
        return '}';
    }

    #[Override]
    protected function getEmptyValue(): string {
        return "{$this->getPrefix()}{$this->getSuffix()}";
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineArguments();
    }

    #[Override]
    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeArguments();
    }

    #[Override]
    protected function block(string|int $key, mixed $item): Block {
        $name = $item->name->value;
        $type = $this->type !== null
            ? $this->getContext()->getField($this->type, $name)?->getType()
            : null;

        return new ObjectField(
            $this->getContext(),
            $item,
            $type,
        );
    }
}
