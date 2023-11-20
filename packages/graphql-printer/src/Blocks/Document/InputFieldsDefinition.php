<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\InputObjectField;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use Override;

/**
 * @internal
 * @extends ListBlock<InputValueDefinition, array-key, InputValueDefinitionNode|InputObjectField>
 */
class InputFieldsDefinition extends ListBlock {
    #[Override]
    protected function getPrefix(): string {
        return '{';
    }

    #[Override]
    protected function getSuffix(): string {
        return '}';
    }

    #[Override]
    protected function isWrapped(): bool {
        return true;
    }

    #[Override]
    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeFields();
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return true;
    }

    #[Override]
    protected function block(string|int $key, mixed $item): Block {
        return new InputValueDefinition($this->getContext(), $item);
    }
}
