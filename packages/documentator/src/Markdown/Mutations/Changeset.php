<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use Override;

/**
 * Changes container.
 */
readonly class Changeset implements Mutation {
    public function __construct(
        /**
         * @var iterable<mixed, array{Location, ?string}>
         */
        protected iterable $changes,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document): iterable {
        return $this->changes;
    }
}
