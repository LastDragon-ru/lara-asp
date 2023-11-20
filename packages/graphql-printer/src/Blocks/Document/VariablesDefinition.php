<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\VariableDefinitionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use Override;

/**
 * @internal
 * @extends ListBlock<VariableDefinition, array-key, VariableDefinitionNode>
 */
class VariablesDefinition extends ListBlock {
    #[Override]
    protected function getPrefix(): string {
        return '(';
    }

    #[Override]
    protected function getSuffix(): string {
        return ')';
    }

    #[Override]
    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeArguments();
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineArguments();
    }

    #[Override]
    protected function block(string|int $key, mixed $item): Block {
        return new VariableDefinition(
            $this->getContext(),
            $item,
        );
    }
}
