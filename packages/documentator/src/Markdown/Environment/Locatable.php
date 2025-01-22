<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;

/**
 * @internal
 * @phpstan-require-extends Node
 */
interface Locatable {
    public function locate(Document $document, Location $location): void;
}
