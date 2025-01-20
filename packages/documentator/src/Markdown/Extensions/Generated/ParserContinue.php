<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\EndMarkerLocation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\StartMarkerLocation;
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
    private Node $block;
    private bool $finished;
    /**
     * @var list<string>
     */
    private array $lines;

    /**
     * @param non-empty-string $line
     * @param non-empty-string $id
     */
    public function __construct(string $line, string $id) {
        $this->block    = new Node($id);
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
        // Start
        $location = LocationData::optional()->get($this->block);
        $start    = $this->getStartMarkerLocation($location);

        if ($start !== null) {
            StartMarkerLocation::set($this->block, $start);
        }

        // End
        $end = $this->getEndMarkerLocation($location);

        if ($end !== null) {
            EndMarkerLocation::set($this->block, $end);
        }
    }

    private function getStartMarkerLocation(?Location $location): ?Location {
        $startLine     = $this->block->getStartLine();
        $startLocation = null;

        if ($location !== null && $startLine !== null) {
            $endLine = $startLine;
            $index   = 1;

            if (str_starts_with($this->lines[$index] ?? '', '[//]: # (warning:')) {
                $endLine++;
                $index++;
            }

            if (($this->lines[$index] ?? '') === '') {
                $endLine++;
            }

            $startLocation = new Location($startLine, $endLine, 0, null, $location->startLinePadding);
        }

        return $startLocation;
    }

    private function getEndMarkerLocation(?Location $location): ?Location {
        $endLine = $this->block->getEndLine();

        if ($location === null || $endLine === null) {
            return null;
        }

        $lines       = count($this->lines);
        $endLocation = null;

        if (str_starts_with($this->lines[$lines - 1], '[//]: # (end:')) {
            $startLine   = $endLine - (int) ($endLine !== ((int) $this->block->getStartLine() + $lines - 1));
            $endLocation = new Location($startLine, $endLine, 0, null, $location->startLinePadding);
        }

        return $endLocation;
    }
}
