<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use Closure;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block as Reference;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;
use Override;
use Stringable;

use function array_slice;
use function count;
use function filter_var;
use function implode;
use function ltrim;
use function mb_substr;
use function preg_match;
use function preg_quote;
use function rawurldecode;
use function rtrim;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_URL;

// todo(documentator): There is no way to convert AST back to Markdown yet
//      https://github.com/thephpleague/commonmark/issues/419

class Document implements Stringable {
    private DocumentNode $node;

    private ?MarkdownParser $parser  = null;
    private ?string         $path    = null;
    private ?string         $title   = null;
    private ?string         $summary = null;

    public function __construct(string $content, ?string $path = null) {
        $this->setContent($content);
        $this->setPath($path);
    }

    public function isEmpty(): bool {
        return !$this->node->hasChildren() && count($this->node->getReferenceMap()) === 0;
    }

    /**
     * Returns the first `# Header` if present.
     */
    public function getTitle(): ?string {
        if ($this->title === null) {
            $title       = $this->getFirstNode($this->node, Heading::class, static fn ($n) => $n->getLevel() === 1);
            $title       = $this->getText($title);
            $title       = trim(ltrim("{$title}", '#')) ?: null;
            $this->title = $title;
        }

        return $this->title;
    }

    /**
     * Returns the first paragraph right after `# Header` if present.
     */
    public function getSummary(): ?string {
        if ($this->summary === null) {
            $title         = $this->getFirstNode($this->node, Heading::class, static fn ($n) => $n->getLevel() === 1);
            $summary       = $this->getText($this->getFirstNode($title?->next(), Paragraph::class));
            $summary       = trim("{$summary}") ?: null;
            $this->summary = $summary;
        }

        return $this->summary;
    }

    public function getPath(): ?string {
        return $this->path;
    }

    /**
     * Changes path and updates all relative links.
     *
     * Please note that links may/will be reformatted (because there is no
     * information about their original form).
     */
    public function setPath(?string $path): static {
        // No path?
        if ($this->path === null || $path === null) {
            $this->path = $path ? Path::normalize($path) : null;

            return $this;
        }

        // Same?
        $path = Path::getPath($this->path, $path);

        if ($this->path === $path) {
            return $this;
        }

        // Update
        $resources = $this->getRelativeResources();
        $changes   = [];
        $editor    = new Editor();
        $lines     = $this->getLines();
        $path      = Path::normalize($path);

        foreach ($resources as $resource) {
            // Location?
            $location = Data::get($resource, LocationData::class);

            if (!$location) {
                continue;
            }

            // Update
            $text   = null;
            $origin = trim((string) $editor->getText($lines, $location));

            if ($resource instanceof Link || $resource instanceof Image) {
                $title        = $resource->getTitle();
                $titleWrapper = mb_substr(rtrim(mb_substr($origin, 0, -1)), -1, 1);
                $label        = (string) Utils::getChild($resource, Text::class)?->getLiteral();
                $target       = rawurldecode($resource->getUrl());
                $target       = Path::getPath($this->path, $target);
                $target       = Path::getRelativePath($path, $target);
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
                $target       = Path::getPath($this->path, $target);
                $target       = Path::getRelativePath($path, $target);
                $targetWrap   = (bool) preg_match('/^\['.preg_quote($resource->getLabel(), '/').']:\s+</u', $origin);
                $text         = Utils::getLink('[%s]: %s %s', $label, $target, $title, $targetWrap, $titleWrapper);
            } else {
                // skipped
            }

            if ($text !== null) {
                $changes[] = [$location, $text];
            }
        }

        // Update
        if ($changes) {
            $lines   = $editor->modify($lines, $changes);
            $content = implode("\n", $lines);

            $this->setContent($content);
        }

        $this->path = $path;

        // Return
        return $this;
    }

    protected function setContent(string $content): static {
        $this->node    = $this->parse($content);
        $this->title   = null;
        $this->summary = null;

        return $this;
    }

    protected function parse(string $string): DocumentNode {
        if (!isset($this->parser)) {
            $converter    = new GithubFlavoredMarkdownConverter();
            $environment  = $converter->getEnvironment()->addExtension(new Extension());
            $this->parser = new MarkdownParser($environment);
        }

        return $this->parser->parse($string);
    }

    /**
     * @return array<array-key, string>
     */
    protected function getLines(): array {
        return Data::get($this->node, Lines::class) ?? [];
    }

    protected function getText(?AbstractBlock $node): ?string {
        if ($node?->getStartLine() === null || $node->getEndLine() === null) {
            return null;
        }

        $start = $node->getStartLine() - 1;
        $end   = $node->getEndLine() - 1;
        $lines = array_slice($this->getLines(), $start, $end - $start + 1);
        $text  = implode("\n", $lines);

        return $text;
    }

    /**
     * @template T of Node
     *
     * @param class-string<T>  $class
     * @param Closure(T): bool $filter
     *
     * @return ?T
     */
    protected function getFirstNode(?Node $node, string $class, ?Closure $filter = null): ?Node {
        // Null?
        if ($node === null) {
            return null;
        }

        // Wanted?
        if ($node instanceof $class && ($filter === null || $filter($node))) {
            return $node;
        }

        // Comment?
        if (
            $node instanceof HtmlBlock
            && str_starts_with($node->getLiteral(), '<!--')
            && str_ends_with($node->getLiteral(), '-->')
        ) {
            return $this->getFirstNode($node->next(), $class, $filter);
        }

        // Document?
        if ($node instanceof DocumentNode) {
            return $this->getFirstNode($node->firstChild(), $class, $filter);
        }

        // Not found
        return null;
    }

    #[Override]
    public function __toString(): string {
        return implode("\n", $this->getLines());
    }

    /**
     * @return list<AbstractWebResource|Reference>
     */
    private function getRelativeResources(): array {
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

        foreach ($this->node->iterator() as $node) {
            // Resource?
            // => we need only which are relative
            // => we don't need references
            if ($node instanceof AbstractWebResource) {
                if (!$node->data->has('reference') && $isRelative($node->getUrl())) {
                    $resources[] = $node;
                }
            }

            // Reference
            // => we need only which are relative
            if ($node instanceof Reference && $isRelative($node->getDestination())) {
                $resources[] = $node;
            }
        }

        return $resources;
    }
}
