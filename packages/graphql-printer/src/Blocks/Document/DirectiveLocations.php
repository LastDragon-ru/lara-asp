<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\UsageList;

/**
 * @internal
 * @extends UsageList<DirectiveLocation, string>
 */
class DirectiveLocations extends UsageList {
    protected function block(mixed $item): Block {
        return new DirectiveLocation(
            $this->getContext(),
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

    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineDirectiveLocations();
    }
}
