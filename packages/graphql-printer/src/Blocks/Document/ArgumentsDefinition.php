<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\Argument;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 * @extends ListBlock<InputValueDefinition>
 */
class ArgumentsDefinition extends ListBlock {
    /**
     * @param iterable<InputValueDefinitionNode>|iterable<Argument> $arguments
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        iterable $arguments,
    ) {
        parent::__construct($context, $level, $used);

        foreach ($arguments as $argument) {
            $name        = $argument instanceof InputValueDefinitionNode
                ? $argument->name->value
                : $argument->name;
            $this[$name] = new InputValueDefinition(
                $this->getContext(),
                $level + 1,
                $used,
                $argument,
            );
        }
    }

    protected function getPrefix(): string {
        return '(';
    }

    protected function getSuffix(): string {
        return ')';
    }

    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeArguments();
    }

    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineArguments();
    }
}
