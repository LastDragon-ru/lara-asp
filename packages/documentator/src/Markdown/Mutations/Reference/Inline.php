<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Content;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use Override;

use function rawurldecode;

/**
 * Inlines all references.
 */
readonly class Inline extends Base {
    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        // Reference?
        if (!$this->isReference($node)) {
            return [];
        }

        // Mutate
        $location = Location::get($node);
        $text     = '';

        if ($node instanceof Link || $node instanceof Image) {
            $content  = Content::get($node);
            $location = $location->moveOffset(($content->offset - $location->offset) + (int) $content->length + 1);
            $title    = Utils::getLinkTitle($node, (string) $node->getTitle());
            $target   = Utils::getLinkTarget($node, rawurldecode($node->getUrl()));
            $text     = $title !== '' ? "({$target} {$title})" : "({$target})";
        }

        return [
            $text !== ''
                ? new Replace($location, $text)
                : new Delete($location),
        ];
    }
}
