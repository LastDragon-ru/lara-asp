<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use GraphQL\Type\Definition\InterfaceType;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;

/**
 * @internal
 * @extends UsageList<TypeBlock, InterfaceType>
 */
class ImplementsInterfacesList extends UsageList {
    protected function block(mixed $item): Block {
        return new TypeBlock(
            $this->getDispatcher(),
            $this->getSettings(),
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
}
