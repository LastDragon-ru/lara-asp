<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Reference\ReferenceInterface;
use Override;

/**
 * @internal
 */
class Reference extends AbstractBlock implements ReferenceInterface {
    public function __construct(
        private readonly ReferenceInterface $reference,
        int $startLine,
        int $endLine,
    ) {
        parent::__construct();

        $this->setStartLine($startLine);
        $this->setEndLine($endLine);
    }

    #[Override]
    public function getLabel(): string {
        return $this->reference->getLabel();
    }

    #[Override]
    public function getDestination(): string {
        return $this->reference->getDestination();
    }

    #[Override]
    public function getTitle(): string {
        return $this->reference->getTitle();
    }
}
