<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 * @extends ListBlock<Argument>
 */
class Arguments extends ListBlock {
    /**
     * @param iterable<ArgumentNode> $arguments
     * @param array<string, Type>    $types
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        iterable $arguments,
        array $types = null,
    ) {
        parent::__construct($context, $level, $used);

        foreach ($arguments as $argument) {
            $name        = $argument->name->value;
            $this[$name] = new Argument(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $argument,
                $types[$name] ?? null,
            );
        }
    }

    protected function getPrefix(): string {
        return '(';
    }

    protected function getSuffix(): string {
        return ')';
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeArguments();
    }

    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineArguments();
    }
}
