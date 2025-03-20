<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Node\Node;
use Override;

use function rawurldecode;

/**
 * Unlink all links to the self.
 */
readonly class UnlinkToSelf extends Unlink {
    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        $url      = rawurldecode($node->getUrl());
        $self     = Utils::isPathRelative($url) && Utils::isPathToSelf($document, $url);
        $mutagens = $self
            ? parent::mutagens($document, $node)
            : [];

        return $mutagens;
    }
}
