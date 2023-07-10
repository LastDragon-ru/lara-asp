<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ObjectBlockList;

/**
 * @internal
 * @extends ObjectBlockList<RootOperationTypeDefinition, string, NamedTypeNode|ObjectType>
 */
class RootOperationTypesDefinition extends ObjectBlockList {
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
        return new RootOperationTypeDefinition(
            $this->getContext(),
            (string) $key,
            $item,
        );
    }
}
