<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use League\CommonMark\Reference\ReferenceMapInterface;
use Override;

/**
 * @internal
 */
class ParserStart implements BlockStartParserInterface {
    private ?ReferenceMapInterface $referenceMap = null;

    public function __construct() {
        // empty
    }

    #[Override]
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart {
        // Maybe?
        if ($cursor->getCurrentCharacter() !== '[') {
            return BlockStart::none();
        }

        // Try
        $parser = new ParserContinue($this->referenceMap);
        $block  = $parser->start($cursor)
            ? BlockStart::of($parser)->at($cursor)
            : BlockStart::none();

        return $block;
    }

    public function setReferenceMap(?ReferenceMapInterface $referenceMap): static {
        $this->referenceMap = $referenceMap;

        return $this;
    }
}
