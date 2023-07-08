<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition as GraphQLFieldDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;

/**
 * @internal
 * @extends ListBlock<FieldDefinition, array-key, FieldDefinitionNode|GraphQLFieldDefinition>
 */
class FieldsDefinition extends ListBlock {
    protected function getPrefix(): string {
        return '{';
    }

    protected function getSuffix(): string {
        return '}';
    }

    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeFields();
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }

    protected function block(string|int $key, mixed $item): Block {
        return new FieldDefinition($this->getContext(), $item);
    }
}
