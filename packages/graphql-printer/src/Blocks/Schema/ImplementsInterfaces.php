<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\InterfaceType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\UsageList;

/**
 * @internal
 * @extends UsageList<Type, InterfaceType>
 */
class ImplementsInterfaces extends UsageList {
    protected function block(mixed $item): Block {
        return new Type(
            $this->getContext(),
            $this->getLevel() + 1,
            $this->getUsed(),
            $item,
        );
    }

    protected function separator(): string {
        return '&';
    }

    protected function prefix(): string {
        return 'implements';
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeInterfaces();
    }

    protected function isAlwaysMultiline(): bool {
        return $this->getSettings()->isAlwaysMultilineInterfaces();
    }
}
