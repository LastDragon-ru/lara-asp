<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document;
use WeakMap;

use function array_slice;
use function preg_split;

/**
 * Fix/Detect location.
 *
 * Out the box only start/end line know. But not for all nodes, for example,
 * `Table`'s nodes don't have this information.
 *
 * @internal
 */
class Locator {
    /**
     * @var WeakMap<AbstractBlock, int>
     */
    private WeakMap $blocks;

    public function __construct(
        private readonly Document $document,
    ) {
        $this->blocks = new WeakMap();
    }

    public function add(AbstractBlock $block, int $padding): void {
        $this->blocks[$block] = $padding;
    }

    public function finalize(): void {
        foreach ($this->blocks as $block => $padding) {
            // Possible?
            $startLine = $block->getStartLine();
            $endLine   = $block->getEndLine();

            if ($startLine === null || $endLine === null) {
                continue;
            }

            // Locate
            $location = LocationData::set($block, new Location($startLine, $endLine, 0, null, $padding));

            if ($block instanceof Table) {
                $this->locateTable($block, $location);
            }

            if ($block instanceof Locatable) {
                $block->locate($this->document, $location);
            }
        }
    }

    private function locateTable(Table $table, Location $location): void {
        $line = $location->startLine;

        for ($section = $table->firstChild(); $section !== null; $section = $section->next()) {
            assert($section instanceof TableSection);

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
}
