<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use Override;

use function preg_quote;

/**
 * @internal
 */
class ParserContinue implements BlockContinueParserInterface {
    private Node $block;
    private bool $finished;

    /**
     * @param non-empty-string $id
     */
    public function __construct(string $id) {
        $this->block    = new Node($id);
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
        $this->finished = $cursor->match($regexp) !== null;

        return BlockContinue::at($cursor);
    }

    #[Override]
    public function addLine(string $line): void {
        // empty
    }

    #[Override]
    public function closeBlock(): void {
        // empty
    }
}
