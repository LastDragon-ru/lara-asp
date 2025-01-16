<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use Override;

use function rawurldecode;

/**
 * Unlink all links to the self.
 */
readonly class UnlinkToSelf extends Unlink implements Mutation {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function nodes(Document $document): iterable {
        foreach (parent::nodes($document) as $key => $node) {
            $url  = rawurldecode($node->getUrl());
            $self = Utils::isPathRelative($url) && Utils::isPathToSelf($document, $url);

            if ($self) {
                yield $key => $node;
            }
        }
    }
}
