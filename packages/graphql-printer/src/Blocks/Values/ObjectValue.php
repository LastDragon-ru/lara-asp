<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 * @extends ListBlock<ObjectField>
 */
class ObjectValue extends ListBlock {
    /**
     * @param (TypeNode&Node)|Type|null $type
     */
    public function __construct(
        Context $context,
        ObjectValueNode $definition,
        protected TypeNode|Type|null $type = null,
    ) {
        parent::__construct($context);

        foreach ($definition->fields as $field) {
            $name = $field->name->value;
            $type = $this->getField($this->type, $name)?->getType();

            $this[$name] = new ObjectField(
                $context,
                $field,
                $type,
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
