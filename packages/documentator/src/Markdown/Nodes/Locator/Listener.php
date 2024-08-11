<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locator;

use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Length;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Padding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\Footnote\Node\Footnote;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\NodeIterator;
use Override;
use Traversable;

use function array_slice;
use function count;
use function iterator_to_array;
use function ltrim;
use function mb_strlen;
use function mb_substr;
use function preg_split;

/**
 * Fix/Detect location/padding.
 *
 * Out the box only start/end line know. But not for all notes, for example,
 * `Table`'s nodes don't have this information. Another important thing -
 * padding of the block node.
 *
 * @internal
 */
class Listener implements EnvironmentAwareInterface {
    private ?EnvironmentInterface $environment = null;

    public function __construct() {
        // empty
    }

    public function __invoke(DocumentParsedEvent $event): void {
        // Fix/Detect
        $document = $event->getDocument();

        foreach ($document->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if ($node instanceof TableSection) {
                $this->fixTableSection($document, $node);
            } elseif ($node instanceof TableRow) {
                $this->fixTableRow($document, $node);
            } elseif ($node instanceof Footnote) {
                $this->fixFootnote($document, $node);
            } else {
                // empty
            }
        }

        // Finalize Locations
        foreach ($this->environment?->getInlineParsers() ?? [] as $parser) {
            if ($parser instanceof Parser) {
                $parser->finalize();
            }
        }
    }

    #[Override]
    public function setEnvironment(EnvironmentInterface $environment): void {
        $this->environment = $environment;
    }

    private function fixTableSection(Document $document, TableSection $section): void {
        // Fixed?
        if ($section->getStartLine() !== null && $section->getEndLine() !== null) {
            return;
        }

        // Fix
        $previous = Cast::toNullable(TableSection::class, $section->previous());
        $rows     = count($this->toArray($section->children()));
        $start    = null;
        $end      = null;

        if ($previous) {
            $start = $previous->getEndLine();

            if ($start !== null) {
                $start = $start + 1 + 1; // Each table has a `|----|` line, thus `+1`.
                $end   = $start + $rows - 1;
            }
        } else {
            $start = Cast::toNullable(Table::class, $section->parent())?->getStartLine();

            if ($start !== null) {
                $end = $start + $rows - 1;
            }
        }

        $section->setStartLine($start);
        $section->setEndLine($end);

        Utils::getPadding($section, $start, '|');
    }

    private function fixTableRow(Document $document, TableRow $row): void {
        // Fixed?
        if (($row->getStartLine() !== null && $row->getEndLine() !== null)) {
            return;
        }

        // Fix
        $line = Cast::toNullable(TableSection::class, $row->parent())?->getStartLine();
        $line = $line !== null
            ? $line + Utils::getPosition($row)
            : null;

        $row->setStartLine($line);
        $row->setEndLine($line);

        if ($line === null) {
            return;
        }

        // Go to Cells?
        $padding = Utils::getPadding($row, $line, '|');
        $text    = Utils::getLine($document, $line);

        if ($padding === null || $text === null) {
            return;
        }

        // Yep
        $cells    = preg_split('/(?<!\\\\)[|]/u', mb_substr($text, $padding)) ?: []; // `|` must be always escaped
        $cells    = array_slice(
            $cells,
            1,
            -1,
        );                                                                           // First and Last characters are `|`, skip them
        $index    = 0;
        $offset   = 1;
        $children = $this->toArray($row->children());

        if (count($children) !== count($cells)) {
            return;
        }

        foreach ($children as $cell) {
            $cell    = Cast::to(TableCell::class, $cell);
            $content = $cells[$index];
            $length  = mb_strlen($content);
            $trimmed = $length - mb_strlen(ltrim($content));

            $cell->setStartLine($line);
            $cell->setEndLine($line);

            Data::set($cell, new BlockPadding($padding));
            Data::set($cell, new Padding($trimmed));
            Data::set($cell, new Offset($offset));
            Data::set($cell, new Length($length));

            $offset += $length + 1;
            $index  += 1;
        }
    }

    private function fixFootnote(Document $document, Footnote $footnote): void {
        // Possible?
        $start = $footnote->getStartLine();
        $end   = $footnote->getEndLine();

        if ($start === null || $end === null) {
            return;
        }

        // Initial
        $initial = Utils::getPadding($footnote, $start, '[^');

        if ($initial === null) {
            return;
        }

        if ($start === $end) {
            return;
        }

        // Internal
        $padding = null;
        $index   = $start + 1;

        do {
            $line    = (string) Utils::getLine($document, $index++);
            $line    = mb_substr($line, $initial);
            $trimmed = ltrim($line);
            $padding = mb_strlen($line) - mb_strlen($trimmed);
        } while ($index < $end && $trimmed === '');

        Data::set($footnote, new Padding($padding));
    }

    /**
     * @template T
     *
     * @param iterable<array-key, T> $iterable
     *
     * @return array<array-key, T>
     */
    private function toArray(iterable $iterable): array {
        return $iterable instanceof Traversable
            ? iterator_to_array($iterable)
            : $iterable;
    }
}
