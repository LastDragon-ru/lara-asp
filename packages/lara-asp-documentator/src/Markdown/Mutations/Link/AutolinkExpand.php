<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Content;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Node\Node;
use Override;

use function mb_substr;
use function rawurldecode;
use function str_starts_with;

/**
 * Converts autolink into normal link.
 *
 * @since 10.0.0
 */
readonly class AutolinkExpand extends Base {
    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        // Autolink?
        $content = Content::optional()->get($node);

        if ($content !== null) {
            return [];
        }

        // Convert
        $url      = rawurldecode($node->getUrl());
        $label    = str_starts_with($url, 'mailto:') ? mb_substr($url, 7) : $url;
        $label    = Utils::escapeTextInTableCell($node, $label);
        $target   = Utils::getLinkTarget($node, $url);
        $content  = "[{$label}]({$target})";
        $location = Location::get($node);

        return [
            new Replace($location, $content),
        ];
    }
}
