<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\UsageList;

/**
 * @internal
 * @extends UsageList<Type, array-key, NamedTypeNode|ObjectType>
 */
class UnionMemberTypes extends UsageList {
    protected function block(string|int $key, mixed $item): Block {
        return new Type(
            $this->getContext(),
            $item,
        );
    }

    protected function separator(): string {
        return '|';
    }

    protected function prefix(): string {
        return '=';
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeUnions();
    }

    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineUnions();
    }
}
