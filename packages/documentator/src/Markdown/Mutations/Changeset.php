<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;

/**
 * Changes container.
 */
readonly class Changeset implements Mutation {
    public function __construct(
        /**
         * @var iterable<array-key, array{Location, ?string}>
         */
        protected iterable $changes,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document, DocumentNode $node): iterable {
        return $this->changes;
    }
}
