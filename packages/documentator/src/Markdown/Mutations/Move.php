<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block as Reference;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;

use function dirname;
use function ltrim;
use function mb_substr;
use function preg_match;
use function preg_quote;
use function rawurldecode;
use function rtrim;
use function trim;

/**
 * Changes path and updates all relative links.
 *
 * Please note that links may/will be reformatted (because there is no
 * information about their original form)
 */
readonly class Move implements Mutation {
    public function __construct(
        protected string $path,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document, DocumentNode $node): iterable {
        // Just in case
        yield from [];

        // No path?
        $docPath = $document->getPath();

        if ($docPath === null) {
            $document->setPath(Path::normalize($this->path));

            return false;
        }

        // Same?
        $docDirectory = dirname($docPath);
        $newPath      = Path::getPath($docDirectory, $this->path);

        if ($docPath === $newPath) {
            return false;
        }

        // Update
        $resources    = $this->getRelativeResources($document, $node);
        $newDirectory = dirname($newPath);

        foreach ($resources as $resource) {
            // Location?
            $location = Utils::getLocation($resource);

            if (!$location) {
                continue;
            }

            // Changes
            $text = null;

            if ($resource instanceof Link || $resource instanceof Image) {
                $offset   = Data::get($resource, Offset::class);
                $location = $offset !== null ? Utils::getOffsetLocation($location, $offset) : null;

                if ($location !== null) {
                    $origin       = trim((string) $document->getText($location));
                    $titleValue   = (string) $resource->getTitle();
                    $titleWrapper = mb_substr(rtrim(mb_substr($origin, 0, -1)), -1, 1);
                    $title        = Utils::getLinkTitle($resource, $titleValue, $titleWrapper);
                    $targetValue  = rawurldecode($resource->getUrl());
                    $targetValue  = Path::getPath($docDirectory, $targetValue);
                    $targetValue  = Path::getRelativePath($newDirectory, $targetValue);
                    $targetWrap   = mb_substr(ltrim(ltrim($origin, '(')), 0, 1) === '<';
                    $target       = Utils::getLinkTarget($resource, $targetValue, $targetWrap);
                    $text         = $title ? "({$target} {$title})" : "({$target})";
                }
            } elseif ($resource instanceof Reference) {
                $origin       = trim((string) $document->getText($location));
                $label        = $resource->getLabel();
                $titleValue   = $resource->getTitle();
                $titleWrapper = mb_substr($origin, -1, 1);
                $title        = Utils::getLinkTitle($resource, $titleValue, $titleWrapper);
                $targetValue  = rawurldecode($resource->getDestination());
                $targetValue  = Path::getPath($docDirectory, $targetValue);
                $targetValue  = Path::getRelativePath($newDirectory, $targetValue);
                $targetWrap   = (bool) preg_match('/^\['.preg_quote($resource->getLabel(), '/').']:\s+</u', $origin);
                $target       = Utils::getLinkTarget($resource, $targetValue, $targetWrap);
                $text         = trim("[{$label}]: {$target} {$title}");

                if ($location->startLine !== $location->endLine) {
                    $padding = $location->internalPadding ?? $location->startLinePadding;
                    $last    = $document->getText(new Coordinate(
                        $location->endLine,
                        $padding,
                        $location->length,
                        $padding,
                    ));

                    if ($last === '') {
                        $text .= "\n";
                    }
                }
            } else {
                // skipped
            }

            if ($location !== null && $text !== null) {
                yield [$location, $text];
            }
        }

        // Set
        $document->setPath($newPath);

        // Return
        return true;
    }

    /**
     * @return list<AbstractWebResource|Reference>
     */
    protected function getRelativeResources(Document $document, DocumentNode $node): array {
        $resources = [];

        foreach ($node->iterator() as $child) {
            $url = null;

            if ($child instanceof AbstractWebResource && !Utils::isReference($child)) {
                $url = rawurldecode($child->getUrl());
            } elseif ($child instanceof Reference) {
                $url = rawurldecode($child->getDestination());
            } else {
                // empty
            }

            if ($url !== null && $this->isRelativeResource($document, $url)) {
                $resources[] = $child;
            }
        }

        return $resources;
    }

    protected function isRelativeResource(Document $document, string $url): bool {
        return Utils::isPathRelative($url) && !Utils::isPathToSelf($url, $document);
    }
}
