<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;

use function rawurldecode;

/**
 * Removes all links to the self.
 */
readonly class SelfLinksRemove implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document, DocumentNode $node): iterable {
        // Just in case
        yield from [];

        // Update
        $links = $this->getLinks($document, $node);

        foreach ($links as $link) {
            // Location & Offset?
            $location = Utils::getLocation($link);
            $offset   = Offset::get($link);

            if ($location === null || $offset === null) {
                continue;
            }

            // Changes
            yield [Utils::getLengthLocation($location, 1), null];           // [
            yield [Utils::getOffsetLocation($location, $offset - 1), null]; // ](...)
        }

        // Return
        return true;
    }

    /**
     * @return list<Link>
     */
    protected function getLinks(Document $document, DocumentNode $node): array {
        $links = [];

        foreach ($node->iterator() as $child) {
            if ($child instanceof Link && $this->isLink($document, rawurldecode($child->getUrl()))) {
                $links[] = $child;
            }
        }

        return $links;
    }

    protected function isLink(Document $document, string $url): bool {
        return Utils::isPathRelative($url) && Utils::isPathToSelf($url, $document);
    }
}
