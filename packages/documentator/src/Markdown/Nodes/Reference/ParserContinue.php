<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference;

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
    private int    $offset;

    public function __construct(
        private readonly ?ReferenceMapInterface $referenceMap,
    ) {
        $this->block  = new Block();
        $this->parser = new Parser();
        $this->offset = 0;
    }

    public function start(Cursor $cursor): bool {
        $this->offset = $cursor->getPosition();
        $started      = $this->parse($cursor);

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
        $reference = $this->parser->getReference();

        $this->block
            ->setReference($reference)
            ->setOffset($this->offset);

        if ($reference && $this->referenceMap && !$this->referenceMap->contains($reference->getLabel())) {
            $this->referenceMap->add($reference);
        }
    }

    private function parse(Cursor $cursor): bool {
        $parsed = $this->parser->parse($cursor->getRemainder());

        if ($parsed) {
            $cursor->advanceToEnd();
        }

        return $parsed;
    }
}
