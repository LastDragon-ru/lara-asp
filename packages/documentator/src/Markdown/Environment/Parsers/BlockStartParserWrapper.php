<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Parsers;

use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Aware;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Locator;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use League\Config\ConfigurationAwareInterface;
use Override;

/**
 * @internal
 */
readonly class BlockStartParserWrapper
    implements BlockStartParserInterface, EnvironmentAwareInterface, ConfigurationAwareInterface {
    use Aware;

    public function __construct(
        private Locator $locator,
        private BlockStartParserInterface $parser,
    ) {
        // empty
    }

    #[Override]
    protected function getParser(): object {
        return $this->parser;
    }

    #[Override]
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart {
        $offset = $cursor->getPosition();
        $start  = $this->parser->tryStart($cursor, $parserState);

        if ($start !== null) {
            $contentOffset = $cursor->getPosition();

            foreach ($start->getBlockParsers() as $parser) {
                $this->locator->addBlock($parser->getBlock(), $offset, $contentOffset);
            }
        }

        return $start;
    }
}
