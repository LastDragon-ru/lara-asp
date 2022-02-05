<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;

/**
 * @internal
 * @extends UsageList<TypeBlock, ObjectType>
 */
class UnionMemberTypesList extends UsageList {
    protected function block(mixed $item): Block {
        return new TypeBlock(
            $this->getSettings(),
            $this->getLevel() + 1,
            $this->getUsed(),
            $item,
        );
    }

    protected function separator(): string {
        return '|';
    }

    protected function prefix(): string {
        return '';
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeUnions();
    }

    protected function isAlwaysMultiline(): bool {
        return $this->getSettings()->isAlwaysMultilineUnions();
    }
}