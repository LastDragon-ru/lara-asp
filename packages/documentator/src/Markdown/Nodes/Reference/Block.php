<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Line;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locationable;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Reference\ReferenceInterface;
use Override;

/**
 * @internal
 */
class Block extends AbstractBlock implements ReferenceInterface, Locationable {
    private ?ReferenceInterface $reference = null;
    private int                 $offset    = 0;

    public function setReference(?ReferenceInterface $reference): static {
        $this->reference = $reference;

        return $this;
    }

    public function getOffset(): int {
        return $this->offset;
    }

    public function setOffset(int $offset): static {
        $this->offset = $offset;

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

    /**
     * @inheritDoc
     */
    #[Override]
    public function getLocation(): iterable {
        // Unknown?
        $start = $this->getStartLine();
        $end   = $this->getEndLine();

        if ($start === null || $end === null) {
            yield from [];

            return;
        }

        // Nope
        for ($i = $start; $i <= $end; $i++) {
            yield new Line($i, $this->getOffset(), null);
        }
    }
}
