<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Misc;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\Argument;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\FieldArgumentDirective;

/**
 * @template TValue
 */
class FieldArgumentValue {
    /**
     * @param FieldArgumentDirective<TValue> $directive
     */
    public function __construct(
        protected readonly Argument|InputValueDefinitionNode $argument,
        protected readonly FieldArgumentDirective $directive,
        protected readonly mixed $value,
        protected readonly bool $passed,
    ) {
        // empty
    }

    public function getName(): string {
        return $this->argument instanceof InputValueDefinitionNode
            ? $this->argument->name->value
            : $this->argument->name;
    }

    public function getArgument(): Argument|InputValueDefinitionNode {
        return $this->argument;
    }

    public function hasValue(): bool {
        return $this->passed;
    }

    /**
     * @return TValue|null
     */
    public function getValue(): mixed {
        return $this->hasValue()
            ? $this->directive->getFieldArgumentValue($this->value)
            : $this->getDefaultValue();
    }

    /**
     * @return TValue
     */
    public function getDefaultValue(): mixed {
        return $this->directive->getFieldArgumentDefault();
    }
}
