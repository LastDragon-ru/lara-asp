<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block as Reference;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Inline\Text;
use Override;

use function dirname;
use function filter_var;
use function mb_substr;
use function preg_match;
use function preg_quote;
use function rawurldecode;
use function rtrim;
use function str_replace;
use function str_starts_with;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_URL;

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
    public function __invoke(Document $document, DocumentNode $node): array {
        // No path?
        $docPath = $document->getPath();

        if ($docPath === null) {
            $document->setPath(Path::normalize($this->path));

            return [];
        }

        // Same?
        $docDirectory = dirname($docPath);
        $newPath      = Path::getPath($docDirectory, $this->path);

        if ($docPath === $newPath) {
            return [];
        }

        // Update
        $changes      = [];
        $resources    = $this->getRelativeResources($node);
        $newDirectory = dirname($newPath);

        foreach ($resources as $resource) {
            // Location?
            $location = Utils::getLocation($resource);

            if (!$location) {
                continue;
            }

            // Changes
            $text   = null;
            $origin = trim((string) $document->getText($location));

            if ($resource instanceof Link || $resource instanceof Image) {
                $title        = $resource->getTitle();
                $titleWrapper = mb_substr(rtrim(mb_substr($origin, 0, -1)), -1, 1);
                $label        = (string) Utils::getChild($resource, Text::class)?->getLiteral();
                $target       = rawurldecode($resource->getUrl());
                $target       = Path::getPath($docDirectory, $target);
                $target       = Path::getRelativePath($newDirectory, $target);
                $targetWrap   = (bool) preg_match('/^!?\['.preg_quote($label, '/').']\(\s*</u', $origin);

                if (Utils::getContainer($resource) instanceof TableCell) {
                    $title  = $title ? str_replace('|', '\\|', $title) : $title;
                    $label  = str_replace('|', '\\|', $label);
                    $target = str_replace('|', '\\|', $target);
                }

                $text = $title
                    ? Utils::getLink('[%s](%s %s)', $label, $target, $title, $targetWrap, $titleWrapper)
                    : Utils::getLink('[%s](%s)', $label, $target, '', $targetWrap, $titleWrapper);

                if ($resource instanceof Image) {
                    $text = "!{$text}";
                }
            } elseif ($resource instanceof Reference) {
                $label        = $resource->getLabel();
                $title        = $resource->getTitle();
                $titleWrapper = mb_substr($origin, -1, 1);
                $target       = rawurldecode($resource->getDestination());
                $target       = Path::getPath($docDirectory, $target);
                $target       = Path::getRelativePath($newDirectory, $target);
                $targetWrap   = (bool) preg_match('/^\['.preg_quote($resource->getLabel(), '/').']:\s+</u', $origin);
                $text         = Utils::getLink('[%s]: %s %s', $label, $target, $title, $targetWrap, $titleWrapper);
            } else {
                // skipped
            }

            if ($text !== null) {
                $changes[] = [$location, $text];
            }
        }

        // Set
        $document->setPath($newPath);

        // Return
        return $changes;
    }

    /**
     * @return list<AbstractWebResource|Reference>
     */
    protected function getRelativeResources(DocumentNode $node): array {
        $resources  = [];
        $isRelative = static function (string $target): bool {
            // Fast
            if (str_starts_with($target, './') || str_starts_with($target, '../')) {
                return true;
            } elseif (str_starts_with($target, '/')) {
                return false;
            } else {
                // empty
            }

            // Long
            return filter_var($target, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) === null
                && !str_starts_with($target, 'tel:+') // see https://www.php.net/manual/en/filter.filters.validate.php
                && !str_starts_with($target, 'urn:')  // see https://www.php.net/manual/en/filter.filters.validate.php
                && Path::isRelative($target);
        };

        foreach ($node->iterator() as $child) {
            // Resource?
            // => we need only which are relative
            // => we don't need references
            if ($child instanceof AbstractWebResource) {
                if (!$child->data->has('reference') && $isRelative($child->getUrl())) {
                    $resources[] = $child;
                }
            }

            // Reference
            // => we need only which are relative
            if ($child instanceof Reference && $isRelative($child->getDestination())) {
                $resources[] = $child;
            }
        }

        return $resources;
    }
}
