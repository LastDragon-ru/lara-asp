<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use Closure;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Editor\Editor;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use Override;
use Stringable;

use function array_key_first;
use function array_key_last;
use function array_values;
use function count;
use function implode;
use function is_int;
use function mb_ltrim;
use function mb_trim;
use function str_ends_with;
use function str_starts_with;

// todo(documentator): There is no way to convert AST back to Markdown yet
//      https://github.com/thephpleague/commonmark/issues/419

class Document implements Stringable {
    private ?Editor $editor  = null;
    private ?string $title   = null;
    private ?string $summary = null;

    public function __construct(
        protected readonly Markdown $markdown,
        public readonly DocumentNode $node,
        public ?FilePath $path = null,
    ) {
        // empty
    }

    public function isEmpty(): bool {
        return !$this->node->hasChildren() && count($this->node->getReferenceMap()) === 0;
    }

    /**
     * Returns the first `# Header` if present.
     */
    public function getTitle(): ?string {
        if ($this->title === null) {
            $title       = $this->getFirstNode(Heading::class, static fn ($n) => $n->getLevel() === 1);
            $title       = $this->getBlockText($title) ?? '';
            $title       = mb_trim(mb_ltrim("{$title}", '#'));
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
            $summary       = mb_trim("{$summary}");
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
        $body    = mb_trim((string) $body);
        $body    = $body !== '' ? $body : null;

        return $body;
    }

    /**
     * @param iterable<array-key, Coordinate> $location
     */
    public function getText(iterable $location): string {
        return (string) $this->getEditor()->extract([$location]);
    }

    public function mutate(Mutation ...$mutations): self {
        $document = clone $this;

        foreach ($mutations as $mutation) {
            $changes  = $mutation($document);
            $content  = mb_trim((string) $document->getEditor()->mutate($changes))."\n";
            $document = $this->markdown->parse($content, $document->path);
        }

        return $document;
    }

    /**
     * @return array<array-key, string>
     */
    protected function getLines(): array {
        return Lines::get($this->node);
    }

    protected function getEditor(): Editor {
        if ($this->editor === null) {
            $lines        = $this->getLines();
            $offset       = (int) array_key_first($lines);
            $this->editor = new Editor(array_values($lines), $offset);
        }

        return $this->editor;
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

        foreach ($this->node->children() as $child) {
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
        return implode("\n", $this->getLines())."\n";
    }
}
