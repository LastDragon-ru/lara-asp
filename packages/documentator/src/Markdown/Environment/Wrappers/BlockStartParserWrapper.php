<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Wrappers;

use League\CommonMark\Parser\Block\BlockContinueParserWithInlinesInterface;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use Override;
use ReflectionProperty;

/**
 * @internal
 */
readonly class BlockStartParserWrapper implements BlockStartParserInterface {
    public function __construct(
        private BlockStartParserInterface $parser,
    ) {
        // empty
    }

    #[Override]
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart {
        $padding = $cursor->getPosition();
        $start   = $this->parser->tryStart($cursor, $parserState);

        if ($start !== null) {
            $parsers = [];

            foreach ($start->getBlockParsers() as $key => $parser) {
                $parsers[$key] = match (true) {
                    $parser instanceof BlockContinueParserWithInlinesInterface
                        => new BlockContinueParserWithInlinesWrapper($parser, $padding),
                    default
                        => new BlockContinueParserWrapper($parser, $padding),
                };
            }

            if ($parsers !== []) {
                (new ReflectionProperty($start, 'blockParsers'))->setValue($start, $parsers);
            }
        }

        return $start;
    }
}
