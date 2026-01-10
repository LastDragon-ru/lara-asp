<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference;

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
    private Node   $block;
    private Parser $parser;
    private bool   $finished;

    public function __construct(
        private readonly ?ReferenceMapInterface $referenceMap,
    ) {
        $this->block    = new Node();
        $this->parser   = new Parser();
        $this->finished = false;
    }

    public function start(Cursor $cursor): bool {
        $this->finished = false;
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
