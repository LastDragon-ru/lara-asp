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

    public function getArgument(): Argument|InputValueDefinitionNode {
        return $this->argument;
    }

    public function isPassed(): bool {
        return $this->passed;
    }

    /**
     * @return TValue|null
     */
    public function getValue(): mixed {
        return $this->isPassed()
            ? $this->directive->getFieldArgumentValue($this->value)
            : null;
    }

    /**
     * @return TValue|null
     */
    public function getDefaultValue(): mixed {
        return $this->directive->getFieldArgumentDefault();
    }
}
