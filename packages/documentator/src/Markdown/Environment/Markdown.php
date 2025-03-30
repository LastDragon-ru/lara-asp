<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Environment;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document as DocumentContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Extension as GeneratedExtension;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Extension as ReferenceExtension;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Parser\MarkdownParserInterface;
use League\CommonMark\Renderer\DocumentRendererInterface;
use League\CommonMark\Renderer\HtmlRenderer;
use Override;
use Psr\EventDispatcher\ListenerProviderInterface;

class Markdown implements MarkdownContract {
    protected readonly EnvironmentInterface      $environment;
    protected readonly MarkdownParserInterface   $parser;
    protected readonly DocumentRendererInterface $renderer;

    public function __construct() {
        $this->environment = $this->environment();
        $this->renderer    = new HtmlRenderer($this->environment);
        $this->parser      = new MarkdownParser($this->environment);
    }

    protected function environment(): EnvironmentInterface&EnvironmentBuilderInterface&ListenerProviderInterface {
        $environment = (new GithubFlavoredMarkdownConverter())->getEnvironment();
        $environment = new Environment($environment);

        foreach ($this->extensions() as $extension) {
            $environment->addExtension($extension);
        }

        return $environment;
    }

    /**
     * @return array<array-key, ExtensionInterface>
     */
    protected function extensions(): array {
        return [
            new GeneratedExtension(),
            new ReferenceExtension(),
            new FootnoteExtension(),
        ];
    }

    #[Override]
    public function parse(string $content, ?FilePath $path = null): DocumentContract {
        return new Document($this, $this->parser, $content, $path);
    }

    #[Override]
    public function render(DocumentContract $document): string {
        return (string) $this->renderer->renderDocument($document->node);
    }
}
