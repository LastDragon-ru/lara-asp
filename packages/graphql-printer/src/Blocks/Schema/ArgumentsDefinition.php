<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\Argument;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Traversable;

/**
 * @internal
 * @extends ListBlock<InputValueDefinition>
 */
class ArgumentsDefinition extends ListBlock {
    /**
     * @param Traversable<Argument>|array<Argument> $arguments
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        Traversable|array $arguments,
    ) {
        parent::__construct($context, $level, $used);

        foreach ($arguments as $argument) {
            $this[$argument->name] = new InputValueDefinition(
                $this->getContext(),
                $this->getLevel() + 1,
                $this->getUsed(),
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
