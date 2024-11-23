<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding as DataBlockPadding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated\Data\EndMarkerLocation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated\Data\StartMarkerLocation;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use Override;

use function count;
use function preg_quote;
use function str_starts_with;

/**
 * @internal
 */
class ParserContinue implements BlockContinueParserInterface {
    private Block $block;
    private bool  $finished;
    /**
     * @var list<string>
     */
    private array $lines;

    /**
     * @param non-empty-string $line
     * @param non-empty-string $id
     */
    public function __construct(string $line, string $id, private int $padding) {
        $this->block    = new Block($id);
        $this->lines    = [$line];
        $this->finished = false;
    }

    #[Override]
    public function getBlock(): AbstractBlock {
        return $this->block;
    }

    #[Override]
    public function isContainer(): bool {
        return true;
    }

    #[Override]
    public function canHaveLazyContinuationLines(): bool {
        return false;
    }

    #[Override]
    public function canContain(AbstractBlock $childBlock): bool {
        return true;
    }

    #[Override]
    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue {
        if ($this->finished) {
            return BlockContinue::finished();
        }

        $id             = preg_quote($this->block->id, '!');
        $regexp         = "!^\[//]: # \(end: {$id}\)$!u";
        $this->lines[]  = $cursor->getRemainder();
        $this->finished = $cursor->match($regexp) !== null;

        return BlockContinue::at($cursor);
    }

    #[Override]
    public function addLine(string $line): void {
        // empty
    }

    #[Override]
    public function closeBlock(): void {
        // Padding
        Data::set($this->block, new DataBlockPadding($this->padding));

        // Start
        $start = $this->getStartMarkerLocation();

        if ($start !== null) {
            Data::set($this->block, new StartMarkerLocation($start));
        }

        // End
        $end = $this->getEndMarkerLocation();

        if ($end !== null) {
            Data::set($this->block, new EndMarkerLocation($end));
        }
    }

    private function getStartMarkerLocation(): ?Location {
        $location  = null;
        $startLine = $this->block->getStartLine();

        if ($startLine !== null) {
            $endLine = $startLine;
            $index   = 1;

            if (str_starts_with($this->lines[$index] ?? '', '[//]: # (warning:')) {
                $endLine++;
                $index++;
            }

            if (($this->lines[$index] ?? '') === '') {
                $endLine++;
            }

            $location = new Location($startLine, $endLine, 0, null, $this->padding);
        }

        return $location;
    }

    private function getEndMarkerLocation(): ?Location {
        $location = null;
        $endLine  = $this->block->getEndLine();

        if ($endLine !== null && str_starts_with($this->lines[count($this->lines) - 1], '[//]: # (end:')) {
            $startLine = $endLine - (int) ($endLine !== ((int) $this->block->getStartLine() + count($this->lines) - 1));
            $location  = new Location($startLine, $endLine, 0, null, $this->padding);
        }

        return $location;
    }
}
