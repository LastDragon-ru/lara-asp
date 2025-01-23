<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Content;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use Override;

use function rawurldecode;

/**
 * Inlines all references.
 */
readonly class Inline extends Base implements Mutation {
    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document): iterable {
        // Just in case
        yield from [];

        // Process
        foreach ($this->nodes($document) as $node) {
            // Change
            $location = Location::get($node);
            $text     = null;

            if ($node instanceof Link || $node instanceof Image) {
                $content  = Content::get($node);
                $location = $location->withOffset(($content->offset - $location->offset) + (int) $content->length + 1);
                $title    = Utils::getLinkTitle($node, (string) $node->getTitle());
                $target   = Utils::getLinkTarget($node, rawurldecode($node->getUrl()));
                $text     = $title !== '' ? "({$target} {$title})" : "({$target})";
            } elseif ($node instanceof ReferenceNode) {
                $text = '';
            } else {
                // skipped
            }

            if ($text !== null) {
                yield [$location, $text !== '' ? $text : null];
            }
        }
    }
}
