<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locator;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\BlockPadding as DataBlockPadding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location as LocationData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Offset as OffsetData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Padding as PaddingData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Aware;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Extension\CommonMark\Parser\Inline\CloseBracketParser;
use League\CommonMark\Extension\Footnote\Node\FootnoteRef;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use League\Config\ConfigurationAwareInterface;
use Override;
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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingTraversableTypeHintSpecification https://github.com/slevomat/coding-standard/issues/1692
     * @var WeakMap<Node, object{origin: int, offset: int, length: int}>
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
        $origin = $cursor->getPosition() + 1;
        $offset = match (true) {
            $this->parser instanceof CloseBracketParser => $this->getDelimiterOffset($inlineContext, ['[', '!']),
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

        if ($child === null) {
            return true;
        }

        // Detect Location
        $startLine = $container->getStartLine();
        $endLine   = $container->getEndLine();
        $length    = $cursor->getPosition() - $offset;
        $line      = $cursor->getLine();

        if ($startLine !== null && $endLine !== null) {
            $start     = $line;
            $delimiter = $origin - $offset;

            if ($startLine !== $endLine) {
                $before           = mb_substr($line, 0, $offset);
                $beforeLines      = Text::getLines($before);
                $beforeLinesCount = count($beforeLines) - 1;
                $inline           = mb_substr($line, $offset, $length);
                $inlineLines      = Text::getLines($inline);
                $inlineLinesCount = count($inlineLines) - 1;
                $startLine        = $startLine + $beforeLinesCount;
                $endLine          = $startLine + $inlineLinesCount;
                $start            = ((string) end($beforeLines)).((string) reset($inlineLines));

                if ($beforeLinesCount > 0) {
                    $correction = (mb_strlen(implode("\n", array_slice($beforeLines, 0, -1))) + 1);
                    $offset    -= $correction;
                    $origin    -= $correction;
                }

                if ($startLine !== $endLine) {
                    $length -= mb_strlen($inline) - $delimiter;
                }
            }

            $padding = Utils::getPadding($child, $startLine, $start);

            if ($padding !== null) {
                $this->save($child, $startLine, $endLine, $offset, $length, $padding, $delimiter);
            }
        } elseif ($container instanceof TableCell) {
            // The properties of the `TableCell` is not known yet (v2.4.2), we
            // should wait until parsing is complete.
            //
            // Also, escaped `|` passed down to inline parsing as an unescaped
            // pipe character. It leads to invalid `$offset`/`$length`.
            $origin                  += mb_substr_count(mb_substr($line, 0, $origin), '|');
            $offset                  += mb_substr_count(mb_substr($line, 0, $offset), '|');
            $length                  += mb_substr_count(mb_substr($line, $offset, $length), '|');
            $this->incomplete[$child] = new class($origin - $offset, $offset, $length) {
                public function __construct(
                    public readonly int $origin,
                    public readonly int $offset,
                    public readonly int $length,
                ) {
                    // empty
                }
            };
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
            $blockPadding   = DataBlockPadding::get($container);
            $cellPadding    = PaddingData::get($container);
            $offset         = OffsetData::get($container);

            if ($blockStartLine === null || $blockEndLine === null) {
                continue;
            }

            // Set
            $this->save(
                $node,
                $blockStartLine,
                $blockEndLine,
                $coordinate->offset + $offset,
                $coordinate->length,
                $blockPadding + $cellPadding,
                $coordinate->origin,
            );
        }

        // Cleanup
        $this->incomplete = new WeakMap();
    }

    /**
     * @param list<string> $characters
     */
    private function getDelimiterOffset(InlineParserContext $context, array $characters): int {
        $delimiter = $context->getDelimiterStack()->searchByCharacter($characters);
        $length    = 0;

        if ($delimiter instanceof DelimiterInterface && $delimiter->isActive()) {
            // We do not use `$delimiter->getLength()` here because `$delimiter->getIndex()`
            // seems incorrect for some delimiters e.g. for `![`.
            $length = (int) $delimiter->getIndex() - mb_strlen($delimiter->getInlineNode()->getLiteral());
        }

        return $length;
    }

    private function save(
        Node $child,
        int $startLine,
        int $endLine,
        int $offset,
        ?int $length,
        int $padding,
        int $origin,
    ): void {
        LocationData::set($child, new Location($startLine, $endLine, $offset, $length, $padding));

        if (!($child instanceof FootnoteRef)) {
            OffsetData::set($child, $origin);
        }
    }
}
