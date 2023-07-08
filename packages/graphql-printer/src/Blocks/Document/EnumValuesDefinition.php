<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ObjectBlockList;

/**
 * @internal
 * @extends ObjectBlockList<EnumValueDefinition, array-key, EnumValueDefinitionNode|GraphQLEnumValueDefinition>
 */
class EnumValuesDefinition extends ObjectBlockList {
    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeEnums();
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }

    protected function block(string|int $key, mixed $item): Block {
        return new EnumValueDefinition(
            $this->getContext(),
            $item,
        );
    }
}
