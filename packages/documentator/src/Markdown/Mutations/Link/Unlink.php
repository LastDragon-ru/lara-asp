<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Content;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use League\CommonMark\Node\Node;
use Override;

/**
 * Unlink all links.
 */
readonly class Unlink extends Base {
    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        $location = Location::get($node);
        $content  = Content::get($node);

        return [
            new Delete($location->withLength(1)),
            new Delete($location->moveOffset(($content->offset - $location->offset) + (int) $content->length)),
        ];
    }
}
