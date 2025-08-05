<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Type\Definition\InterfaceType;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\UsageList;
use Override;

/**
 * @internal
 * @extends UsageList<Type, array-key, NamedTypeNode|InterfaceType>
 */
class ImplementsInterfaces extends UsageList {
    #[Override]
    protected function block(string|int $key, mixed $item): Block {
        return new Type(
            $this->getContext(),
            $item,
        );
    }

    #[Override]
    protected function separator(): string {
        return '&';
    }

    #[Override]
    protected function prefix(): string {
        return 'implements';
    }

    #[Override]
    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeInterfaces();
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return $this->getSettings()->isAlwaysMultilineInterfaces();
    }
}
