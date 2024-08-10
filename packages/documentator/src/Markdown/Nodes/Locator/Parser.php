<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locator;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPaddingInitial;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Data;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Padding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Locator;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Aware;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\DelimiterStack;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Extension\CommonMark\Parser\Inline\CloseBracketParser;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use League\Config\ConfigurationAwareInterface;
use Override;
use ReflectionProperty;
use WeakMap;

use function array_slice;
use function count;
use function end;
use function implode;
use function mb_strlen;
use function mb_substr;
use function mb_substr_count;
use function reset;

/**
 * Inline parser that saves location of the parsed node (= last parsed node).
 *
 * @update(league/commonmark): Check {@see Environment::injectEnvironmentAndConfigurationIfNeeded()}.
 *
 * @internal
 *
 * @see Environment
 */
class Parser implements InlineParserInterface, EnvironmentAwareInterface, ConfigurationAwareInterface {
    use Aware;

    /**
     * @var WeakMap<Node, Coordinate>
     */
    private WeakMap $incomplete;

    public function __construct(
        private readonly InlineParserInterface $parser,
    ) {
        $this->incomplete = new WeakMap();
    }

    #[Override]
    protected function getObject(): object {
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
        $offset = $cursor->getPosition();

        if ($this->parser instanceof CloseBracketParser) {
            $offset = $offset
                - $this->getDelimiterStackLength($inlineContext->getDelimiterStack()) // delimiters length
                - mb_strlen($cursor->getPreviousText());                              // text after delimiter
        }

        // Parse
        $parsed = $this->parser->parse($inlineContext);

        if (!$parsed) {
            return false;
        }

        // Detect Location
        $container = $inlineContext->getContainer();
        $startLine = $container->getStartLine();
        $endLine   = $container->getEndLine();
        $length    = $cursor->getPosition() - $offset;
        $child     = $container->lastChild();
        $line      = $cursor->getLine();

        if ($child !== null && $startLine !== null && $endLine !== null) {
            $start = $line;

            if ($startLine !== $endLine) {
                $before           = mb_substr($line, 0, $offset);
                $beforeLines      = Text::getLines($before);
                $beforeLinesCount = count($beforeLines) - 1;
                $inline           = mb_substr($line, $offset, $length);
                $inlineLines      = Text::getLines($inline);
                $inlineLinesCount = count($inlineLines) - 1;
                $startLine        = $startLine + $beforeLinesCount;
                $endLine          = $startLine + $inlineLinesCount;
                $start            = (end($beforeLines) ?: '').(reset($inlineLines) ?: '');

                if ($beforeLinesCount) {
                    $offset -= (mb_strlen(implode("\n", array_slice($beforeLines, 0, -1))) + 1);
                }

                if ($startLine !== $endLine) {
                    $length -= mb_strlen($inline);
                }
            }

            $padding = Utils::getPadding($child, $startLine, $start);

            if ($padding !== null) {
                Data::set($child, new Location(new Locator($startLine, $endLine, $offset, $length, $padding)));
            }
        } elseif ($child !== null && $container instanceof TableCell) {
            // The properties of the `TableCell` is not known yet (v2.4.2), we
            // should wait until parsing is complete.
            //
            // Also, escaped `|` passed down to inline parsing as an unescaped
            // pipe character. It leads to invalid `$offset`/`$length`.
            $offset                  += mb_substr_count(mb_substr($line, 0, $offset), '|');
            $length                  += mb_substr_count(mb_substr($line, $offset, $length), '|');
            $this->incomplete[$child] = new Coordinate(-1, $offset, $length);
        } else {
            // empty
        }

        // Ok
        return true;
    }

    public function finalize(): void {
        // Complete detection
        foreach ($this->incomplete as $node => $coordinate) {
            // Container?
            $container = Utils::getContainer($node);

            if ($container === null) {
                continue;
            }

            // Detected?
            $blockStartLine = $container->getStartLine();
            $blockEndLine   = $container->getEndLine();
            $blockPadding   = Data::get($container, BlockPaddingInitial::class);
            $cellPadding    = Data::get($container, Padding::class);
            $offset         = Data::get($container, Offset::class);

            if (
                $blockStartLine === null
                || $blockEndLine === null
                || $blockPadding === null
                || $cellPadding === null
                || $offset === null
            ) {
                continue;
            }

            // Set
            Data::set(
                $node,
                new Location(
                    new Locator(
                        $blockStartLine,
                        $blockEndLine,
                        $coordinate->offset + $offset,
                        $coordinate->length,
                        $blockPadding + $cellPadding,
                    ),
                ),
            );
        }

        // Cleanup
        $this->incomplete = new WeakMap();
    }

    private function getDelimiterStackLength(DelimiterStack $stack): int {
        $delimiter = (new ReflectionProperty($stack, 'top'))->getValue($stack);
        $length    = $delimiter instanceof DelimiterInterface
            ? mb_strlen($delimiter->getInlineNode()->getLiteral())
            : 0;

        return $length;
    }
}
