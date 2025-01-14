<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Content;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use Override;

/**
 * Unlink all links.
 */
readonly class Unlink extends Mutation {
    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document): iterable {
        // Just in case
        yield from [];

        // Update
        foreach ($this->nodes($document) as $node) {
            $location = Location::get($node);
            $content  = Content::get($node);

            yield [$location->withLength(1), null];                                                               // [
            yield [$location->withOffset(($content->offset - $location->offset) + (int) $content->length), null]; // ](...)
        }

        // Return
        return true;
    }
}
