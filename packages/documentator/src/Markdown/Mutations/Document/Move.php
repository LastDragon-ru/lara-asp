<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference as ReferenceData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Text;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Finalize;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\Path\Path;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image as ImageNode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link as LinkNode;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use Override;

use function mb_ltrim;
use function mb_strlen;
use function mb_substr;
use function parse_url;
use function rawurldecode;

use const PHP_URL_PATH;

/**
 * Changes path and updates all relative links.
 *
 * Please note that links may/will be reformatted (because there is no
 * information about their original form)
 *
 * @implements Mutation<DocumentNode|LinkNode|ImageNode|ReferenceNode>
 */
readonly class Move implements Mutation {
    public function __construct(
        protected FilePath $path,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function nodes(): array {
        return [
            DocumentNode::class,
            LinkNode::class,
            ImageNode::class,
            ReferenceNode::class,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        $mutagens = [];

        if ($node instanceof DocumentNode) {
            if ($document->path !== null) {
                $mutagens[] = new Finalize(function (Document $document): void {
                    $document->path = $document->path?->resolve($this->path);
                });
            } else {
                $mutagens[] = new Finalize(function (Document $document): void {
                    $document->path = $this->path->normalized();
                });
            }
        } elseif ($document->path === null || $document->path->equals($document->path->resolve($this->path))) {
            // Path already equal to new path -> skip
        } elseif (!$this->isPathRelative($node)) {
            // Path is not relative -> skip
        } elseif ($node instanceof AbstractWebResource && ReferenceData::get($node) !== null) {
            // Nothing to do
        } else {
            $destination = $node instanceof ReferenceNode
                ? $node->getDestination()
                : $node->getUrl();
            $location    = $node instanceof ReferenceNode
                ? Utils::getReferenceDestinationLocation($document, $node)
                : Utils::getLinkDestinationLocation($document, $node);
            $origin      = (string) $document->mutate(new Text($location));
            $target      = $document->path->resolve($this->path);
            $value       = $this->target($document, $document->path, $target, $destination);
            $wrap        = mb_substr(mb_ltrim($origin), 0, 1) === '<';
            $text        = Utils::getLinkTarget($node, $value, $wrap !== false ? true : null);
            $mutagens[]  = new Replace($location, $text);
        }

        return $mutagens;
    }

    private function isPathRelative(LinkNode|ImageNode|ReferenceNode $node): bool {
        $url      = $node instanceof AbstractWebResource ? $node->getUrl() : $node->getDestination();
        $url      = rawurldecode($url);
        $relative = Utils::isPathRelative($url);

        return $relative;
    }

    private function target(Document $document, FilePath $docPath, FilePath $newPath, string $target): string {
        $target = rawurldecode($target);

        if (Utils::isPathToSelf($document, $target)) {
            $path   = (string) parse_url($target, PHP_URL_PATH);
            $target = mb_substr($target, mb_strlen($path));
            $target = $target !== '' ? $target : '#';
        } else {
            $target = $docPath->resolve(Path::make($target));
            $target = $newPath->relative($target);
        }

        return (string) $target;
    }
}
