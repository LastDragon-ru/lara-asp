<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Input as InputData;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Parsers\BlockStartParserWrapper;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Parsers\InlineParserWrapper;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorCollection;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Extension\CommonMark\Parser\Inline\AutolinkParser;
use League\CommonMark\Extension\CommonMark\Parser\Inline\BacktickParser;
use League\CommonMark\Extension\CommonMark\Parser\Inline\CloseBracketParser;
use League\CommonMark\Extension\CommonMark\Parser\Inline\HtmlInlineParser;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Footnote\Parser\FootnoteRefParser;
use League\CommonMark\Normalizer\TextNormalizerInterface;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Block\SkipLinesStartingWithLettersParser;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\Config\ConfigurationInterface;
use Override;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * @internal
 */
class Environment implements EnvironmentInterface, EnvironmentBuilderInterface, ListenerProviderInterface {
    private ?Locator $locator = null;

    public function __construct(
        private readonly EnvironmentInterface&EnvironmentBuilderInterface&ListenerProviderInterface $environment,
    ) {
        $environment->addEventListener(DocumentPreParsedEvent::class, function (DocumentPreParsedEvent $event): void {
            $input         = new Input($event->getMarkdown()->getContent());
            $document      = $event->getDocument();
            $this->locator = new Locator($document);

            $event->replaceMarkdown($input);

            InputData::set($document, $event->getMarkdown());
        });
        $environment->addEventListener(DocumentParsedEvent::class, function (): void {
            $this->locator?->locate();

            $this->locator = null;
        });
    }

    #[Override]
    public function getConfiguration(): ConfigurationInterface {
        return $this->environment->getConfiguration();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getExtensions(): iterable {
        return $this->environment->getExtensions();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getBlockStartParsers(): iterable {
        // Locator?
        $parsers = $this->environment->getBlockStartParsers();
        $locator = $this->locator;

        if ($locator === null) {
            return $parsers;
        }

        // Wrap to find block location
        foreach ($parsers as $key => $parser) {
            if ($parser instanceof EnvironmentAwareInterface) {
                $parser->setEnvironment($this);
            }

            if (!($parser instanceof SkipLinesStartingWithLettersParser)) {
                $parser = new BlockStartParserWrapper($locator, $parser);
            }

            yield $key => $parser;
        }

        // Just in case
        yield from [];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getInlineParsers(): iterable {
        // Locator?
        $parsers = $this->environment->getInlineParsers();
        $locator = $this->locator;

        if ($locator === null) {
            return $parsers;
        }

        // Wrap to find inline location
        foreach ($parsers as $key => $parser) {
            if ($parser instanceof EnvironmentAwareInterface) {
                $parser->setEnvironment($this);
            }

            if (
                $parser instanceof CloseBracketParser
                || $parser instanceof AutolinkParser
                || $parser instanceof BacktickParser
                || $parser instanceof FootnoteRefParser
                || $parser instanceof HtmlInlineParser
            ) {
                $parser = new InlineParserWrapper($locator, $parser);
            }

            yield $key => $parser;
        }

        // Just in case
        yield from [];
    }

    #[Override]
    public function getDelimiterProcessors(): DelimiterProcessorCollection {
        return $this->environment->getDelimiterProcessors();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getRenderersForClass(string $nodeClass): iterable {
        return $this->environment->getRenderersForClass($nodeClass);
    }

    #[Override]
    public function getSlugNormalizer(): TextNormalizerInterface {
        return $this->environment->getSlugNormalizer();
    }

    #[Override]
    public function dispatch(object $event): mixed {
        return $this->environment->dispatch($event);
    }

    #[Override]
    public function addExtension(ExtensionInterface $extension): static {
        $this->environment->addExtension($extension);

        return $this;
    }

    #[Override]
    public function addBlockStartParser(BlockStartParserInterface $parser, int $priority = 0): static {
        $this->environment->addBlockStartParser($parser, $priority);

        return $this;
    }

    #[Override]
    public function addInlineParser(InlineParserInterface $parser, int $priority = 0): static {
        $this->environment->addInlineParser($parser, $priority);

        return $this;
    }

    #[Override]
    public function addDelimiterProcessor(DelimiterProcessorInterface $processor): static {
        $this->environment->addDelimiterProcessor($processor);

        return $this;
    }

    #[Override]
    public function addRenderer(string $nodeClass, NodeRendererInterface $renderer, int $priority = 0): static {
        $this->environment->addRenderer($nodeClass, $renderer, $priority);

        return $this;
    }

    /**
     * @param class-string     $eventClass
     * @param callable():mixed $listener
     */
    #[Override]
    public function addEventListener(string $eventClass, callable $listener, int $priority = 0): static {
        $this->environment->addEventListener($eventClass, $listener, $priority);

        return $this;
    }

    /**
     * @return iterable<mixed, mixed>
     */
    #[Override]
    public function getListenersForEvent(object $event): iterable {
        return $this->environment->getListenersForEvent($event);
    }
}
