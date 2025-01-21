<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Parsers;

use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Locator;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Aware;
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
    protected function getObject(): object {
        return $this->parser;
    }

    #[Override]
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart {
        $padding = $cursor->getPosition();
        $start   = $this->parser->tryStart($cursor, $parserState);

        if ($start !== null) {
            foreach ($start->getBlockParsers() as $parser) {
                $this->locator->add($parser->getBlock(), $padding);
            }
        }

        return $start;
    }
}
