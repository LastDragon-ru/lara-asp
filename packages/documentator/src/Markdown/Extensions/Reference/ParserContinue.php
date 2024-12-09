<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Reference\ReferenceMapInterface;
use Override;

/**
 * @internal
 */
class ParserContinue implements BlockContinueParserInterface {
    private Block  $block;
    private Parser $parser;
    private int    $padding;
    private bool   $finished;

    public function __construct(
        private readonly ?ReferenceMapInterface $referenceMap,
    ) {
        $this->block    = new Block();
        $this->parser   = new Parser();
        $this->padding  = 0;
        $this->finished = false;
    }

    public function start(Cursor $cursor): bool {
        $this->finished = false;
        $this->padding  = $cursor->getPosition();
        $started        = $this->parse($cursor);

        return $started;
    }

    #[Override]
    public function getBlock(): AbstractBlock {
        return $this->block;
    }

    #[Override]
    public function isContainer(): bool {
        return false;
    }

    #[Override]
    public function canHaveLazyContinuationLines(): bool {
        return false;
    }

    #[Override]
    public function canContain(AbstractBlock $childBlock): bool {
        return false;
    }

    #[Override]
    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue {
        return $this->parse($cursor)
            ? BlockContinue::at($cursor)
            : BlockContinue::none();
    }

    #[Override]
    public function addLine(string $line): void {
        if ($line !== '') {
            $this->parser->parse($line);
        }
    }

    #[Override]
    public function closeBlock(): void {
        // Reference
        $reference = $this->parser->getReference();

        $this->block->setReference($reference);

        if (
            $reference !== null
            && $this->referenceMap !== null
            && !$this->referenceMap->contains($reference->getLabel())
        ) {
            $this->referenceMap->add($reference);
        }

        // Data
        BlockPadding::set($this->block, $this->padding);
    }

    private function parse(Cursor $cursor): bool {
        if ($this->finished) {
            return false;
        }

        $line           = $cursor->getRemainder();
        $parsed         = $this->parser->parse($line);
        $this->finished = $parsed !== true;

        if ($parsed) {
            $cursor->advanceToEnd();
        }

        return $parsed || $line === '';
    }
}
