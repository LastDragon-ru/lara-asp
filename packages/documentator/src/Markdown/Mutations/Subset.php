<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Extraction;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use Override;

/**
 * Extractions container.
 */
readonly class Subset implements Extraction {
    public function __construct(
        /**
         * @var iterable<mixed, iterable<mixed, Coordinate>>
         */
        protected iterable $extractions,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document): iterable {
        return $this->extractions;
    }
}
