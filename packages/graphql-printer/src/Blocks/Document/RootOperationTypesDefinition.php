<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\ObjectBlockList;
use Override;

/**
 * @internal
 * @extends ObjectBlockList<RootOperationTypeDefinition, string, NamedTypeNode|ObjectType>
 */
class RootOperationTypesDefinition extends ObjectBlockList {
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
        return new RootOperationTypeDefinition(
            $this->getContext(),
            (string) $key,
            $item,
        );
    }
}
