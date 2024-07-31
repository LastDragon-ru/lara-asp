<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locator;

use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Lines;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Padding;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locator;
use LastDragon_ru\LaraASP\Documentator\Utils\Text;
use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\DelimiterStack;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Override;
use ReflectionProperty;

use function array_slice;
use function count;
use function end;
use function implode;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;
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
    public function __construct(
        private readonly InlineParserInterface $parser,
    ) {
        // empty
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
        $offset = $cursor->getPosition()
            - $this->getDelimiterStackLength($inlineContext->getDelimiterStack()) // delimiters length
            - mb_strlen($cursor->getPreviousText());                              // text after delimiter
        $parsed = $this->parser->parse($inlineContext);

        if ($parsed) {
            $container = $inlineContext->getContainer();
            $startLine = $container->getStartLine();
            $endLine   = $container->getEndLine();
            $child     = $container->lastChild();

            if ($child !== null && $startLine !== null && $endLine !== null) {
                $length = $cursor->getPosition() - $offset;
                $line   = $cursor->getLine();
                $tail   = $line;

                if ($startLine !== $endLine) {
                    $before           = mb_substr($line, 0, $offset);
                    $beforeLines      = Text::getLines($before);
                    $beforeLinesCount = count($beforeLines) - 1;
                    $inline           = mb_substr($line, $offset, $length);
                    $inlineLines      = Text::getLines($inline);
                    $inlineLinesCount = count($inlineLines) - 1;
                    $startLine        = $startLine + $beforeLinesCount;
                    $endLine          = $startLine + $inlineLinesCount;
                    $tail             = (end($beforeLines) ?: '').(reset($inlineLines) ?: '');

                    if ($beforeLinesCount) {
                        $offset -= (mb_strlen(implode("\n", array_slice($beforeLines, 0, -1))) + 1);
                    }

                    if ($startLine !== $endLine) {
                        $length -= mb_strlen($inline);
                    }
                }

                $padding = $this->getBlockPadding($child, $startLine, $tail);

                if ($padding !== null) {
                    $child->data->set(
                        Location::class,
                        new Location(
                            new Locator($startLine, $endLine, $offset, $length, $padding),
                        ),
                    );
                }
            }
        }

        return $parsed;
    }

    #[Override]
    public function setEnvironment(EnvironmentInterface $environment): void {
        if ($this->parser instanceof EnvironmentAwareInterface) {
            $this->parser->setEnvironment($environment);
        }
    }

    #[Override]
    public function setConfiguration(ConfigurationInterface $configuration): void {
        if ($this->parser instanceof ConfigurationAwareInterface) {
            $this->parser->setConfiguration($configuration);
        }
    }

    private function getDelimiterStackLength(DelimiterStack $stack): int {
        $delimiter = (new ReflectionProperty($stack, 'top'))->getValue($stack);
        $length    = 0;

        if ($delimiter instanceof DelimiterInterface) {
            $length += $delimiter->getLength();
        }

        return $length;
    }

    private function getBlockPadding(Node $node, int $line, string $tail): ?int {
        // Search for Document
        $document = null;
        $padding  = null;
        $parent   = $node;
        $block    = null;

        do {
            // Document?
            if ($parent instanceof Document) {
                $document = $parent;
                break;
            }

            // Cached?
            if ($parent instanceof AbstractBlock && $block === null) {
                $block = $parent;

                if ($block->data->has(Padding::class)) {
                    $padding = Cast::toNullable(Padding::class, $block->data->get(Padding::class, null))?->value;

                    if ($padding !== null) {
                        break;
                    }
                }
            }

            // Deep
            $parent = $parent->parent();
        } while ($parent);

        if ($document === null) {
            return $padding;
        }

        // Detect block padding
        // (we are expecting that all lines inside block have the same padding)
        $lines   = $document->data->get(Lines::class, null);
        $lines   = $lines instanceof Lines ? $lines->get() : [];
        $padding = mb_strpos($lines[$line] ?? '', $tail);

        if ($padding === false) {
            return null;
        }

        // Cache
        if ($block) {
            $block->data->set(Padding::class, new Padding($padding));
        }

        // Return
        return $padding;
    }
}
