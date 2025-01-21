<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document;
use WeakMap;

/**
 * @internal
 */
class Locator {
    /**
     * @var WeakMap<AbstractBlock, int>
     */
    private WeakMap $blocks;

    public function __construct(
        private readonly Document $document,
    ) {
        $this->blocks = new WeakMap();
    }

    public function add(AbstractBlock $block, int $padding): void {
        $this->blocks[$block] = $padding;
    }

    public function finalize(): void {
        foreach ($this->blocks as $block => $padding) {
            // Possible?
            $startLine = $block->getStartLine();
            $endLine   = $block->getEndLine();

            if ($startLine === null || $endLine === null) {
                continue;
            }

            // Locate
            $location = LocationData::set($block, new Location($startLine, $endLine, 0, null, $padding));

            if ($block instanceof Locatable) {
                $block->locate($this->document, $location);
            }
        }
    }
}
