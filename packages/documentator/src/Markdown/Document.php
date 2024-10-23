<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use Closure;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;
use Override;
use Stringable;

use function array_key_first;
use function array_key_last;
use function array_values;
use function count;
use function implode;
use function is_int;
use function is_string;
use function ltrim;
use function str_ends_with;
use function str_starts_with;
use function trim;

// todo(documentator): There is no way to convert AST back to Markdown yet
//      https://github.com/thephpleague/commonmark/issues/419

class Document implements Stringable {
    private DocumentNode|string $node;

    private ?MarkdownParser $parser  = null;
    private ?Editor         $editor  = null;
    private ?FilePath       $path    = null;
    private ?string         $title   = null;
    private ?string         $summary = null;

    public function __construct(string $content, ?FilePath $path = null) {
        $this->setContent($content);
        $this->setPath($path);
    }

    public function isEmpty(): bool {
        return !$this->getNode()->hasChildren() && count($this->getNode()->getReferenceMap()) === 0;
    }

    /**
     * Returns the first `# Header` if present, the title based on filename
     * if known, or `null`.
     */
    public function getTitle(): ?string {
        if ($this->title === null) {
            $title       = $this->getFirstNode(Heading::class, static fn ($n) => $n->getLevel() === 1);
            $title       = $this->getBlockText($title) ?? '';
            $title       = trim(ltrim("{$title}", '#'));
            $this->title = $title;
        }

        return $this->title !== '' ? $this->title : null;
    }

    /**
     * Returns the first paragraph if present.
     */
    public function getSummary(): ?string {
        if ($this->summary === null) {
            $summary       = $this->getSummaryNode();
            $summary       = $this->getBlockText($summary);
            $summary       = trim("{$summary}");
            $this->summary = $summary;
        }

        return $this->summary !== '' ? $this->summary : null;
    }

    /**
     * Returns the rest of the document text after the summary.
     */
    public function getBody(): ?string {
        $summary = $this->getSummaryNode();
        $start   = $summary?->getEndLine();
        $end     = array_key_last($this->getLines());
        $body    = $start !== null && is_int($end)
            ? $this->getText(new Location($start + 1, $end))
            : null;
        $body    = trim((string) $body);
        $body    = $body !== '' ? $body : null;

        return $body;
    }

    public function getPath(): ?FilePath {
        return $this->path;
    }

    public function setPath(?FilePath $path): static {
        $this->path = $path;

        return $this;
    }

    public function getText(Location|Coordinate $location): ?string {
        return $this->getEditor()->getText($location);
    }

    /**
     * @return new<static>
     */
    public function mutate(Mutation ...$mutations): static {
        $document = clone $this;

        foreach ($mutations as $mutation) {
            $changes  = $mutation($document, $document->getNode());
            $content  = trim((string) $document->getEditor()->mutate($changes))."\n";
            $document = $document->setContent($content);
        }

        return $document;
    }

    protected function setContent(string $content): static {
        $this->node    = $content;
        $this->title   = null;
        $this->summary = null;
        $this->editor  = null;

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
        return Data::get($this->getNode(), Lines::class) ?? [];
    }

    protected function getEditor(): Editor {
        if ($this->editor === null) {
            $lines        = $this->getLines();
            $offset       = (int) array_key_first($lines);
            $this->editor = new Editor(array_values($lines), $offset);
        }

        return $this->editor;
    }

    public function getNode(): DocumentNode {
        if (is_string($this->node)) {
            $this->node = $this->parse($this->node);
        }

        return $this->node;
    }

    /**
     * @template T of Node
     *
     * @param class-string<T>          $class
     * @param Closure(T): bool|null    $filter
     * @param Closure(Node): bool|null $skip
     *
     * @return ?T
     */
    private function getFirstNode(string $class, ?Closure $filter = null, ?Closure $skip = null): ?Node {
        $node = null;

        foreach ($this->getNode()->children() as $child) {
            // Comment?
            if (
                $child instanceof HtmlBlock
                && str_starts_with($child->getLiteral(), '<!--')
                && str_ends_with($child->getLiteral(), '-->')
            ) {
                continue;
            }

            // Skipped?
            if ($skip !== null && $skip($child)) {
                continue;
            }

            // Wanted?
            if ($child instanceof $class) {
                if ($filter === null || $filter($child)) {
                    $node = $child;
                }

                break;
            }

            // End
            break;
        }

        return $node;
    }

    private function getBlockText(?AbstractBlock $node): ?string {
        $startLine = $node?->getStartLine();
        $endLine   = $node?->getEndLine();
        $location  = $startLine !== null && $endLine !== null
            ? new Location($startLine, $endLine)
            : null;
        $text      = $location !== null
            ? $this->getText($location)
            : null;

        return $text;
    }

    private function getSummaryNode(): ?Paragraph {
        $skip = static fn ($node) => $node instanceof Heading && $node->getLevel() === 1;
        $node = $this->getFirstNode(Paragraph::class, skip: $skip);

        return $node;
    }

    #[Override]
    public function __toString(): string {
        return is_string($this->node) ? $this->node : implode("\n", $this->getLines())."\n";
    }
}
