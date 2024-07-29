<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use Closure;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locationable;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block as Reference;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\ParserStart as ReferenceStartParser;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;
use Override;
use Stringable;

use function array_filter;
use function array_slice;
use function count;
use function filter_var;
use function implode;
use function ltrim;
use function mb_substr;
use function preg_match;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function strtr;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_URL;

// todo(documentator): There is no way to convert AST back to Markdown yet
//      https://github.com/thephpleague/commonmark/issues/419

class Document implements Stringable {
    /**
     * @var array<int, string>
     */
    private array        $lines;
    private DocumentNode $node;

    private ?string $path    = null;
    private ?string $title   = null;
    private ?string $summary = null;

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
        $lines     = $this->lines;
        $path      = Path::normalize($path);
        $getUrl    = static function (string $url): string {
            return preg_match('/\s/u', $url)
                ? '<'.strtr($url, ['<' => '\\\\<', '>' => '\\\\>']).'>'
                : $url;
        };
        $getText   = static function (string $text): string {
            return strtr($text, ['[' => '\\\\[', ']' => '\\\\]']);
        };
        $getTitle  = static function (string $title): string {
            if ($title === '') {
                // no action
            } elseif (!str_contains($title, '(') && !str_contains($title, ')')) {
                $title = "({$title})";
            } elseif (!str_contains($title, '"')) {
                $title = "\"{$title}\"";
            } elseif (!str_contains($title, "'")) {
                $title = "'{$title}'";
            } else {
                $title = '('.strtr($title, ['(' => '\\\\(', ')' => '\\\\)']).')';
            }

            return $title;
        };
        $replace   = static function (array &$lines, Locationable $block, string $text): void {
            // Replace lines
            $last   = null;
            $line   = null;
            $text   = Text::getLines($text);
            $index  = 0;
            $number = null;

            foreach ($block->getLocation() as $location) {
                $last   = $location;
                $number = $location->number - 1;
                $line   = $lines[$number] ?? '';
                $prefix = mb_substr($line, 0, $location->offset);
                $suffix = $location->length
                    ? mb_substr($line, $location->offset + $location->length)
                    : '';

                if (isset($text[$index])) {
                    $lines[$number] = $prefix.$text[$index].$suffix;
                } else {
                    $lines[$number] = null;
                }

                $index++;
            }

            // Parser uses the empty line right after the block as an End Line.
            // We should preserve it.
            if ($last !== null) {
                $content = mb_substr($line, $last->offset);

                if ($content === '') {
                    $lines[$number] = mb_substr($line, 0, $last->offset);
                }
            }
        };

        foreach ($resources as $resource) {
            if ($resource instanceof Reference) {
                $origin = Path::getPath($this->path, $resource->getDestination());
                $target = $getUrl(Path::getRelativePath($path, $origin));
                $label  = $getText($resource->getLabel());
                $title  = $getTitle($resource->getTitle());
                $text   = trim("[{$label}]: {$target} {$title}");

                $replace($lines, $resource, $text);
            }
        }

        // Update
        if ($resources) {
            $this->setContent(
                implode("\n", array_filter($lines, static fn ($line) => $line !== null)),
            );
        }

        $this->path = $path;

        // Return
        return $this;
    }

    protected function setContent(string $content): static {
        $this->node    = $this->parse($content);
        $this->lines   = Text::getLines($content);
        $this->title   = null;
        $this->summary = null;

        return $this;
    }

    protected function parse(string $string): DocumentNode {
        $converter   = new GithubFlavoredMarkdownConverter();
        $environment = $converter->getEnvironment()
            ->addBlockStartParser(new ReferenceStartParser(), 250);
        $parser      = new MarkdownParser($environment);

        return $parser->parse($string);
    }

    protected function getText(?AbstractBlock $node): ?string {
        if ($node?->getStartLine() === null || $node->getEndLine() === null) {
            return null;
        }

        $start = $node->getStartLine() - 1;
        $end   = $node->getEndLine() - 1;
        $lines = array_slice($this->lines, $start, $end - $start + 1);
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
        return implode("\n", $this->lines);
    }

    /**
     * @return list<AbstractWebResource|Reference>
     */
    private function getRelativeResources(): array {
        $resources  = [];
        $isRelative = static function (string $target): bool {
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
