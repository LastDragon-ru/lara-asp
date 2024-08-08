<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use Closure;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Locator;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;
use Override;
use Stringable;

use function count;
use function implode;
use function ltrim;
use function str_ends_with;
use function str_starts_with;
use function trim;

// todo(documentator): There is no way to convert AST back to Markdown yet
//      https://github.com/thephpleague/commonmark/issues/419

class Document implements Stringable {
    private DocumentNode $node;

    private ?MarkdownParser $parser  = null;
    private ?Editor         $editor  = null;
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
            $title       = $title?->getStartLine() !== null && $title->getEndLine() !== null
                ? $this->getText(new Locator($title->getStartLine(), $title->getEndLine()))
                : null;
            $title       = trim(ltrim("{$title}", '#'));
            $this->title = $title;
        }

        return $this->title ?: null;
    }

    /**
     * Returns the first paragraph right after `# Header` if present.
     */
    public function getSummary(): ?string {
        if ($this->summary === null) {
            $title         = $this->getFirstNode($this->node, Heading::class, static fn ($n) => $n->getLevel() === 1);
            $summary       = $this->getFirstNode($title?->next(), Paragraph::class);
            $summary       = $summary?->getStartLine() !== null && $summary->getEndLine() !== null
                ? $this->getText(new Locator($summary->getStartLine(), $summary->getEndLine()))
                : null;
            $summary       = trim("{$summary}");
            $this->summary = $summary;
        }

        return $this->summary ?: null;
    }

    public function getPath(): ?string {
        return $this->path;
    }

    public function setPath(?string $path): static {
        $this->path = $path ? Path::normalize($path) : null;

        return $this;
    }

    public function getText(Location $location): ?string {
        return $this->getEditor()->getText($location);
    }

    /**
     * @return new<static>
     */
    public function mutate(Mutation $mutation): static {
        $document = clone $this;
        $changes  = $mutation($document, $this->node);

        if ($changes) {
            $document->setContent(
                (string) $this->getEditor()->mutate($changes),
            );
        }

        return $document;
    }

    protected function setContent(string $content): static {
        $this->node    = $this->parse($content);
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
        return Data::get($this->node, Lines::class) ?? [];
    }

    protected function getEditor(): Editor {
        if ($this->editor === null) {
            $this->editor = new Editor($this->getLines());
        }

        return $this->editor;
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
}
