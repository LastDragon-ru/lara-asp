<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Editor\Editor;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use League\CommonMark\Node\Block\Document as DocumentNode;
use Override;
use Stringable;

use function count;
use function implode;
use function mb_trim;

// todo(documentator): There is no way to convert AST back to Markdown yet
//      https://github.com/thephpleague/commonmark/issues/419

class Document implements Stringable {
    private ?Editor $editor = null;

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
     * @param iterable<array-key, Coordinate> $location
     */
    public function getText(iterable $location): string {
        return (string) $this->getEditor()->extract([$location]);
    }

    public function mutate(Mutation ...$mutations): self {
        $document = clone $this;

        foreach ($mutations as $mutation) {
            $content  = $document->getEditor()->mutate($mutation($document));
            $content  = mb_trim((string) $content);
            $document = $this->markdown->parse($content, $document->path);
        }

        return $document;
    }

    /**
     * @return array<int, string>
     */
    protected function getLines(): array {
        return Lines::get($this->node);
    }

    protected function getEditor(): Editor {
        if ($this->editor === null) {
            $this->editor = new Editor($this->getLines());
        }

        return $this->editor;
    }

    #[Override]
    public function __toString(): string {
        $lines  = $this->getLines();
        $string = $lines !== []
            ? implode("\n", $this->getLines())."\n"
            : '';

        return $string;
    }
}
