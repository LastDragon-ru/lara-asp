<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\Argument;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;

/**
 * @internal
 * @extends ListBlock<InputValueDefinition, array-key, InputValueDefinitionNode|Argument>
 */
class ArgumentsDefinition extends ListBlock {
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

    protected function block(string|int $key, mixed $item): Block {
        return new InputValueDefinition($this->getContext(), $item);
    }
}
