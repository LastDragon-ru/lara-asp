<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Wrappers;

use League\CommonMark\Parser\Block\BlockContinueParserWithInlinesInterface;
use League\CommonMark\Parser\InlineParserEngineInterface;
use Override;

/**
 * @internal
 *
 * @extends BlockContinueParserWrapper<BlockContinueParserWithInlinesInterface>
 */
class BlockContinueParserWithInlinesWrapper
    extends BlockContinueParserWrapper
    implements BlockContinueParserWithInlinesInterface {
    #[Override]
    public function parseInlines(InlineParserEngineInterface $inlineParser): void {
        $this->parser->parseInlines($inlineParser);
    }
}
