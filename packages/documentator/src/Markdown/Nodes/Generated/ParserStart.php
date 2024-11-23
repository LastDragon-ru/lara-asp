<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated;

use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use Override;

use function mb_strlen;
use function preg_match;

/**
 * @internal
 */
class ParserStart implements BlockStartParserInterface {
    public function __construct() {
        // empty
    }

    #[Override]
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart {
        // Impossible?
        if ($cursor->isIndented() || $cursor->getCurrentCharacter() !== '[') {
            return BlockStart::none();
        }

        // Nested?
        if ($parserState->getActiveBlockParser() instanceof ParserContinue) {
            return BlockStart::none();
        }

        if (Utils::getParent($parserState->getActiveBlockParser()->getBlock(), Block::class) !== null) {
            return BlockStart::none();
        }

        // Match?
        $padding = $cursor->getPosition();
        $matches = [];

        if (
            !(preg_match('!^\[//]: # \(start: ([^)]+)\)($|\R)!u', $cursor->getRemainder(), $matches) > 0)
            || $matches[0] === ''
        ) {
            return BlockStart::none();
        }

        // Yep
        $cursor->advanceBy(mb_strlen($matches[0]));

        return BlockStart::of(new ParserContinue($matches[0], $matches[1], $padding))->at($cursor);
    }
}
