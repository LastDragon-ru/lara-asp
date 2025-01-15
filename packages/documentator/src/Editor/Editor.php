<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Editor;

use LastDragon_ru\LaraASP\Documentator\Editor\Mutators\Extractor;
use LastDragon_ru\LaraASP\Documentator\Editor\Mutators\Mutator;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use Override;
use Stringable;

use function implode;
use function is_string;

readonly class Editor implements Stringable {
    /**
     * @var list<string>
     */
    protected array     $lines;
    protected Mutator   $mutator;
    protected Extractor $extractor;

    /**
     * @param list<string>|string $content
     */
    final public function __construct(
        array|string $content,
        protected int $startLine = 0,
        protected string $endOfLine = "\n",
    ) {
        $this->lines     = is_string($content) ? Text::getLines($content) : $content;
        $this->mutator   = new Mutator();
        $this->extractor = new Extractor();
    }

    #[Override]
    public function __toString(): string {
        return implode($this->endOfLine, $this->lines);
    }

    /**
     * @param iterable<mixed, iterable<mixed, Coordinate>> $locations
     *
     * @return new<static>
     */
    public function extract(iterable $locations): static {
        $extracted = ($this->extractor)($this->lines, $locations, $this->startLine);
        $editor    = new static($extracted, $this->startLine, $this->endOfLine);

        return $editor;
    }

    /**
     * @param iterable<mixed, array{iterable<mixed, Coordinate>, ?string}> $changes
     *
     * @return new<static>
     */
    public function mutate(iterable $changes): static {
        $mutated = ($this->mutator)($this->lines, $changes, $this->startLine);
        $editor  = new static($mutated, $this->startLine, $this->endOfLine);

        return $editor;
    }
}
