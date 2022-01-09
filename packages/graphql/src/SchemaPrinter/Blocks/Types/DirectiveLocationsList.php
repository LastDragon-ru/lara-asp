<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use Traversable;

use function mb_strlen;

/**
 * @internal
 * @extends UsageList<DirectiveLocationBlock, string>
 */
class DirectiveLocationsList extends UsageList {
    protected function block(mixed $item): Block {
        return new DirectiveLocationBlock(
            $this->getDispatcher(),
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
        return 'on';
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeDirectiveLocations();
    }
}
