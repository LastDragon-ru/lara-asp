<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Closure;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;
use Override;
use Stringable;

use function array_slice;
use function count;
use function implode;
use function ltrim;
use function preg_split;
use function str_ends_with;
use function str_starts_with;
use function trim;

// todo(documentator): There is no way to convert AST back to Markdown yet
//      https://github.com/thephpleague/commonmark/issues/419

class MarkdownDocument implements Stringable {
    /**
     * @var array<int, string>
     */
    private array    $lines;
    private Document $node;

    private ?string $title   = null;
    private ?string $summary = null;

    public function __construct(string $string) {
        $this->node  = $this->parse($string);
        $this->lines = preg_split('/\R/u', $string) ?: [];
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

    protected function parse(string $string): Document {
        $converter   = new GithubFlavoredMarkdownConverter();
        $environment = $converter->getEnvironment();
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
        if ($node instanceof Document) {
            return $this->getFirstNode($node->firstChild(), $class, $filter);
        }

        // Not found
        return null;
    }

    #[Override]
    public function __toString(): string {
        return implode("\n", $this->lines);
    }
}
