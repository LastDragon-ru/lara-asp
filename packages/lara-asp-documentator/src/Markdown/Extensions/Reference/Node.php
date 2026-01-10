<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Reference\ReferenceInterface;
use Override;

/**
 * @internal
 */
class Node extends AbstractBlock implements ReferenceInterface {
    private ?ReferenceInterface $reference = null;

    public function setReference(?ReferenceInterface $reference): static {
        $this->reference = $reference;

        return $this;
    }

    public function getReference(): ?ReferenceInterface {
        return $this->reference;
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
}
