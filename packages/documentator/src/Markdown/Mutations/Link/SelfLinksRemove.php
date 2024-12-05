<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use Override;

use function rawurldecode;

/**
 * Removes all links to the self.
 */
readonly class SelfLinksRemove extends Remove {
    #[Override]
    protected function isLink(Document $document, Link $node): bool {
        $url = rawurldecode($node->getUrl());

        return Utils::isPathRelative($url) && Utils::isPathToSelf($url, $document);
    }
}
