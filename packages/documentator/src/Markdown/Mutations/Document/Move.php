<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Content as ContentData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Reference as ReferenceData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node as ReferenceNode;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Text;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Finalize;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image as ImageNode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link as LinkNode;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use Override;

use function mb_ltrim;
use function mb_rtrim;
use function mb_strlen;
use function mb_substr;
use function mb_trim;
use function parse_url;
use function preg_match;
use function preg_quote;
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
                    $document->path = $document->path?->getPath($this->path);
                });
            } else {
                $mutagens[] = new Finalize(function (Document $document): void {
                    $document->path = $this->path->getNormalizedPath();
                });
            }
        } elseif ($document->path === null || $document->path->isEqual($document->path->getPath($this->path))) {
            // Path already equal to new path -> skip
        } elseif (!$this->isPathRelative($node)) {
            // Path is not relative -> skip
        } elseif ($node instanceof AbstractWebResource && ReferenceData::get($node) === null) {
            $path         = $document->path->getPath($this->path);
            $content      = ContentData::get($node);
            $location     = LocationData::get($node);
            $location     = $location->moveOffset(($content->offset - $location->offset) + (int) $content->length + 1);
            $origin       = mb_trim((string) $document->mutate(new Text($location)));
            $titleValue   = (string) $node->getTitle();
            $titleWrapper = mb_substr(mb_rtrim(mb_substr($origin, 0, -1)), -1, 1);
            $title        = Utils::getLinkTitle($node, $titleValue, $titleWrapper);
            $targetValue  = $this->target($document, $document->path, $path, $node->getUrl());
            $targetWrap   = mb_substr(mb_ltrim(mb_ltrim($origin, '(')), 0, 1) === '<';
            $target       = Utils::getLinkTarget($node, $targetValue, $targetWrap);
            $text         = $title !== '' ? "({$target} {$title})" : "({$target})";
            $mutagens[]   = new Replace($location, $text);
        } elseif ($node instanceof ReferenceNode) {
            $path         = $document->path->getPath($this->path);
            $location     = LocationData::get($node);
            $origin       = mb_trim((string) $document->mutate(new Text($location)));
            $label        = $node->getLabel();
            $titleValue   = $node->getTitle();
            $titleWrapper = mb_substr($origin, -1, 1);
            $title        = Utils::getLinkTitle($node, $titleValue, $titleWrapper);
            $targetValue  = $this->target($document, $document->path, $path, $node->getDestination());
            $targetWrap   = (bool) preg_match('/^\['.preg_quote($node->getLabel(), '/').']:\s+</u', $origin);
            $target       = Utils::getLinkTarget($node, $targetValue, $targetWrap);
            $text         = mb_trim("[{$label}]: {$target} {$title}");

            if ($location->startLine !== $location->endLine) {
                $padding = $location->internalPadding ?? $location->startLinePadding;
                $last    = (string) $document->mutate(new Text(new Location(
                    $location->endLine,
                    $location->endLine,
                    $padding,
                    $location->length,
                    $padding,
                )));

                if ($last === '') {
                    $text .= "\n";
                }
            }

            $mutagens[] = new Replace($location, $text);
        } else {
            // empty
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
            $target = $docPath->getFilePath($target);
            $target = $newPath->getRelativePath($target);
        }

        return (string) $target;
    }
}
