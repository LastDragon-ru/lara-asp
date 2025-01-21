<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Parsers;

use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Aware;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Locator;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Extension\CommonMark\Parser\Inline\CloseBracketParser;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Inline\AbstractInline;
use League\CommonMark\Node\StringContainerInterface;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use League\Config\ConfigurationAwareInterface;
use Override;

use function array_slice;
use function count;
use function end;
use function implode;
use function mb_strlen;
use function mb_substr;

/**
 * todo(documentator): [league/commonmark] [update] Check {@see Environment::injectEnvironmentAndConfigurationIfNeeded()}.
 */

/**
 * Inline parser that saves location of the parsed node (= last parsed node).
 *
 * @internal
 */
class InlineParserWrapper implements InlineParserInterface, EnvironmentAwareInterface, ConfigurationAwareInterface {
    use Aware;

    public function __construct(
        private readonly Locator $locator,
        private readonly InlineParserInterface $parser,
    ) {
        // empty
    }

    #[Override]
    protected function getParser(): object {
        return $this->parser;
    }

    #[Override]
    public function getMatchDefinition(): InlineParserMatch {
        return $this->parser->getMatchDefinition();
    }

    #[Override]
    public function parse(InlineParserContext $inlineContext): bool {
        // The `$cursor->getPosition()` depends on delimiters length, we need to
        // find it. Not sure that this is the best way...
        $cursor = $inlineContext->getCursor();
        $origin = $cursor->getPosition() + 1;
        $offset = match (true) {
            $this->parser instanceof CloseBracketParser => $this->getDelimiterOffset($inlineContext),
            default                                     => $cursor->getPosition(),
        };

        // Parsed?
        $parsed = $this->parser->parse($inlineContext);

        if (!$parsed) {
            return false;
        }

        // Child?
        $container = $inlineContext->getContainer();
        $child     = $container->lastChild();

        if (!($child instanceof AbstractInline)) {
            return true;
        }

        // Detect Location
        $delimiter        = $origin - $offset;
        $length           = $cursor->getPosition() - $offset;
        $line             = $cursor->getLine();
        $before           = mb_substr($line, 0, $offset);
        $beforeLines      = Text::getLines($before);
        $beforeLinesCount = count($beforeLines) - 1;
        $inline           = mb_substr($line, $offset, $length);
        $inlineLines      = Text::getLines($inline);
        $inlineLinesCount = count($inlineLines) - 1;
        $startLine        = $beforeLinesCount;
        $endLine          = $startLine + $inlineLinesCount;
        $start            = ((string) end($beforeLines));

        if ($beforeLinesCount > 0) {
            $correction = (mb_strlen(implode("\n", array_slice($beforeLines, 0, -1))) + 1);
            $offset     -= $correction;
            $origin     -= $correction;
        }

        if ($startLine !== $endLine) {
            $length -= mb_strlen($inline) - $delimiter;
        }

        // TableCell?
        if ($container instanceof TableCell) {
            // Escaped `|` passed down to inline parsing as an unescaped
            // pipe character. It leads to invalid `$offset`/`$length`.
            $offset += mb_substr_count(mb_substr($line, 0, $offset), '|');
            $length += mb_substr_count(mb_substr($line, $offset, $length), '|');
        }

        // Save
        $this->locator->addInline($child, $startLine, $endLine, $offset, $length, $start);

        // Ok
        return true;
    }

    private function getDelimiterOffset(InlineParserContext $context): int {
        $offset  = 0;
        $bracket = $context->getDelimiterStack()->getLastBracket();
        $node    = $bracket?->getNode();

        if ($bracket !== null && $node instanceof StringContainerInterface) {
            $offset = $bracket->getPosition() - mb_strlen($node->getLiteral());
        }

        return $offset;
    }
}
