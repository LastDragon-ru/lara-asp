<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\AbstractInline;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\Block\ParagraphParser;
use WeakMap;

use function array_key_first;
use function array_slice;
use function assert;
use function mb_strpos;
use function preg_split;

// todo(documentator): Internal padding for Location

/**
 * Fix/Detect location.
 *
 * Out the box only start/end line know. But not for all nodes, for example,
 * `Table`'s nodes don't have this information. Also, unfortunately, we cannot
 * wrap {@see ParagraphParser} to track {@see Paragraph} location like for all
 * other {@see AbstractBlock} nodes. This is why we still need to iterate over
 * all nodes.
 *
 * @internal
 * @see ParagraphParser
 *
 */
class Locator {
    /**
     * @var WeakMap<AbstractBlock, int>
     */
    private WeakMap $blocks;
    /**
     * @var WeakMap<AbstractInline, array{int, int, int, int, string}>
     */
    private WeakMap $inlines;

    public function __construct(
        private readonly Document $document,
    ) {
        $this->blocks  = new WeakMap();
        $this->inlines = new WeakMap();
    }

    public function addBlock(AbstractBlock $block, int $offset): void {
        $this->blocks[$block] = $offset;
    }

    public function addInline(
        AbstractInline $node,
        int $startLine,
        int $endLine,
        int $offset,
        int $length,
        string $origin,
    ): void {
        $this->inlines[$node] = [$startLine, $endLine, $offset, $length, $origin];
    }

    public function locate(): void {
        $this->locateNode($this->document);
    }

    private function locateNode(Node $node): bool {
        $location = $this->getNodeLocation($node);

        if ($location !== null || $node instanceof AbstractInline) {
            for ($child = $node->firstChild(); $child !== null; $child = $child->next()) {
                $this->locateNode($child);
            }
        }

        return $location !== null;
    }

    private function getNodeLocation(Node $node): ?Location {
        $location = match (true) {
            $node instanceof AbstractInline => $this->getInlineLocation($node),
            $node instanceof Paragraph      => $this->getParagraphLocation($node),
            $node instanceof Document       => $this->getDocumentLocation($node),
            $node instanceof Table          => $this->getTableLocation($node),
            $node instanceof TableSection   => $this->getDataLocation($node),
            $node instanceof TableRow       => $this->getDataLocation($node),
            $node instanceof TableCell      => $this->getDataLocation($node),
            $node instanceof AbstractBlock  => $this->getBlockLocation($node),
            default                         => null
        };

        if ($location !== null) {
            LocationData::set($node, $location);

            if ($node instanceof Locatable) {
                $node->locate($this->document, $location);
            }
        }

        return $location;
    }

    private function getDocumentLocation(Document $node): ?Location {
        $lines     = Lines::get($node);
        $startLine = array_key_first($lines);
        $endLine   = array_key_last($lines);
        $location  = $startLine !== null && $endLine !== null
            ? new Location($startLine, $endLine)
            : null;

        return $location;
    }

    private function getParagraphLocation(Paragraph $node): ?Location {
        // Possible?
        $startLine = $node->getStartLine();
        $endLine   = $node->getEndLine();

        if ($startLine === null || $endLine === null) {
            return null;
        }

        // Content offset can be determined?
        $offset = null;
        $parent = $node->parent();

        if ($parent instanceof Node) {
            $offset = 0;

            do {
                $offset += $this->getNodeOffset($parent);
                $parent  = $parent->parent();
            } while ($parent instanceof Node);
        }

        if ($offset === null) {
            return null;
        }

        // Create
        return new Location($startLine, $endLine, $offset);
    }

    private function getBlockLocation(AbstractBlock $node): ?Location {
        // Possible?
        $startLine = $node->getStartLine();
        $endLine   = $node->getEndLine();
        $offset    = $this->blocks[$node] ?? null;

        if ($startLine === null || $endLine === null || $offset === null) {
            return null;
        }

        // Create
        return new Location($startLine, $endLine, 0, null, $offset);
    }

    private function getInlineLocation(AbstractInline $node): ?Location {
        // Known?
        $info = $this->inlines[$node] ?? null;

        if ($info === null) {
            return null;
        }

        // Parent?
        $block         = Utils::getContainer($node);
        $blockLocation = $block !== null ? LocationData::get($block) : null;

        if ($blockLocation === null) {
            return null;
        }

        // Create
        [$inlineStartLine, $inlineEndLine, $offset, $length, $origin] = $info;

        $blockPadding = $blockLocation->startLine !== $inlineStartLine
            ? ($blockLocation->internalPadding ?? $blockLocation->startLinePadding)
            : $blockLocation->startLinePadding;
        $startLine    = $blockLocation->startLine + $inlineStartLine;
        $endLine      = $blockLocation->startLine + $inlineEndLine;
        $line         = Lines::get($this->document)[$startLine] ?? '';
        $line         = mb_substr($line, $blockPadding);
        $offset       = $offset + $blockPadding + (int) mb_strpos($line, $origin);
        $location     = new Location($startLine, $endLine, $offset, $length);

        // Cleanup
        unset($this->inlines[$node]);

        // Return
        return $location;
    }

    private function getDataLocation(Node $node): ?Location {
        return LocationData::optional()->get($node);
    }

    private function getTableLocation(Table $node): ?Location {
        // Location
        $location = $this->getBlockLocation($node);

        if ($location === null) {
            return null;
        }

        // Sections/Rows/Cells don't have required information to determine
        // location. We need to iterate over them to find their location.
        $this->locateTable($node, $location);

        // Return
        return $location;
    }

    private function locateTable(Table $table, Location $location): void {
        $line = $location->startLine;

        for ($section = $table->firstChild(); $section !== null; $section = $section->next()) {
            assert($section instanceof TableSection);

            LocationData::set(
                $section,
                new Location(
                    $line,
                    $line + (int) $section->isHead(),
                    0,
                    null,
                    $location->startLinePadding,
                ),
            );

            for ($row = $section->firstChild(); $row !== null; $row = $row->next()) {
                assert($row instanceof TableRow);

                $rowLocation = new Location($line, $line, 0, null, $location->startLinePadding);
                $rowLocation = LocationData::set($row, $rowLocation);

                $this->locateTableRow($row, $rowLocation);

                $line++;
            }

            if ($section->isHead()) {
                $line++;  // Each table has a `|----|` line, thus `+1`.
            }
        }
    }

    private function locateTableRow(TableRow $row, Location $location): void {
        // Line?
        $line = Lines::get($this->document)[$location->startLine] ?? '';
        $line = mb_substr($line, $location->startLinePadding);

        if ($line === '') {
            return;
        }

        // Locate
        $cells  = preg_split('/(?<!\\\\)[|]/u', $line);  // `|` must be always escaped
        $cells  = $cells !== false ? $cells : [];
        $cells  = array_slice($cells, 1, -1);            // First&Last characters are `|`, skip
        $offset = $location->startLinePadding + 1;

        for ($cell = $row->firstChild(), $index = 0; $cell !== null; $cell = $cell->next(), $index++) {
            assert($cell instanceof TableCell);

            $cellLength   = mb_strlen($cells[$index] ?? '');
            $cellLocation = new Location($location->startLine, $location->endLine, $offset, $cellLength);

            LocationData::set($cell, $cellLocation);

            $offset += $cellLength + 1;
        }
    }

    private function getNodeOffset(Node $node): int {
        return match (true) {
            $node instanceof BlockQuote => 2,
            default                     => 0,
        };
    }
}
