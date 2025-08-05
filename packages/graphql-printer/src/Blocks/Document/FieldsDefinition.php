<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition as GraphQLFieldDefinition;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\ListBlock;
use Override;

/**
 * @internal
 * @extends ListBlock<FieldDefinition, array-key, FieldDefinitionNode|GraphQLFieldDefinition>
 */
class FieldsDefinition extends ListBlock {
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
        return new FieldDefinition($this->getContext(), $item);
    }
}
