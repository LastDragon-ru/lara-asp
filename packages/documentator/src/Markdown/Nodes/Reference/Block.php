<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locator;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Reference\ReferenceInterface;
use Override;

/**
 * @internal
 */
class Block extends AbstractBlock implements ReferenceInterface {
    private ?ReferenceInterface $reference = null;
    private int                 $padding   = 0;

    public function setReference(?ReferenceInterface $reference): static {
        $this->reference = $reference;

        return $this;
    }

    public function getPadding(): int {
        return $this->padding;
    }

    public function setPadding(int $padding): static {
        $this->padding = $padding;

        return $this;
    }

    #[Override]
    public function getLabel(): string {
        return $this->reference?->getLabel() ?? '';
    }

    #[Override]
    public function getDestination(): string {
        return $this->reference?->getDestination() ?? '';
    }

    #[Override]
    public function getTitle(): string {
        return $this->reference?->getTitle() ?? '';
    }

    public function getLocation(): ?Location {
        // Unknown?
        $start = $this->getStartLine();
        $end   = $this->getEndLine();

        if ($start === null || $end === null) {
            return null;
        }

        // Nope
        return new Location(new Locator($start, $end, 0, null, $this->getPadding()));
    }
}
