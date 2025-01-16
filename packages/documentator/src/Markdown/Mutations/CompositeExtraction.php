<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Extraction;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use Override;

/**
 * Merges all extractions into one.
 */
readonly class CompositeExtraction implements Extraction {
    /**
     * @var array<array-key, Extraction>
     */
    private array $extractions;

    public function __construct(Extraction ...$extractions) {
        $this->extractions = $extractions;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document): iterable {
        // Just in case
        yield from [];

        // Process all
        foreach ($this->extractions as $extraction) {
            yield from $extraction($document);
        }
    }
}
