<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment;

use League\CommonMark\Delimiter\Processor\DelimiterProcessorCollection;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Normalizer\TextNormalizerInterface;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\Config\ConfigurationInterface;
use Override;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * @internal
 */
class Environment implements EnvironmentInterface, EnvironmentBuilderInterface, ListenerProviderInterface {
    public function __construct(
        private readonly EnvironmentInterface&EnvironmentBuilderInterface&ListenerProviderInterface $environment,
    ) {
        // empty
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
        return $this->environment->getBlockStartParsers();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getInlineParsers(): iterable {
        return $this->environment->getInlineParsers();
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
