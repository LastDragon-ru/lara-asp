<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Wrappers;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use Override;

/**
 * @internal
 *
 * @template T of BlockContinueParserInterface
 */
class BlockContinueParserWrapper implements BlockContinueParserInterface {
    public function __construct(
        /**
         * @var T
         */
        protected BlockContinueParserInterface $parser,
        protected int $padding = 0,
    ) {
        // empty
    }

    #[Override]
    public function getBlock(): AbstractBlock {
        return $this->parser->getBlock();
    }

    #[Override]
    public function isContainer(): bool {
        return $this->parser->isContainer();
    }

    #[Override]
    public function canHaveLazyContinuationLines(): bool {
        return $this->parser->canHaveLazyContinuationLines();
    }

    #[Override]
    public function canContain(AbstractBlock $childBlock): bool {
        return $this->parser->canContain($childBlock);
    }

    #[Override]
    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue {
        return $this->parser->tryContinue($cursor, $activeBlockParser);
    }

    #[Override]
    public function addLine(string $line): void {
        $this->parser->addLine($line);
    }

    #[Override]
    public function closeBlock(): void {
        // Location
        $block     = $this->parser->getBlock();
        $startLine = $block->getStartLine();
        $endLine   = $block->getEndLine();

        if ($startLine !== null && $endLine !== null) {
            LocationData::set($block, new Location($startLine, $endLine, 0, null, $this->padding));
        }

        // Close
        $this->parser->closeBlock();
    }
}
