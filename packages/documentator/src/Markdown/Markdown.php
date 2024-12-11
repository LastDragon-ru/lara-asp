<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Core\Extension as CoreExtension;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Extension as GeneratedExtension;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Locator\Extension as LocatorExtension;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Extension as ReferenceExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Parser\MarkdownParserInterface;
use League\CommonMark\Renderer\DocumentRendererInterface;
use League\CommonMark\Renderer\HtmlRenderer;
use Override;

class Markdown implements MarkdownContract {
    protected readonly EnvironmentInterface      $environment;
    protected readonly MarkdownParserInterface   $parser;
    protected readonly DocumentRendererInterface $renderer;

    public function __construct() {
        $this->environment = $this->initialize();
        $this->renderer    = new HtmlRenderer($this->environment);
        $this->parser      = new MarkdownParser($this->environment);
    }

    protected function initialize(): Environment {
        return (new GithubFlavoredMarkdownConverter())->getEnvironment()
            ->addExtension(new FootnoteExtension())
            ->addExtension(new CoreExtension())
            ->addExtension(new GeneratedExtension())
            ->addExtension(new LocatorExtension())
            ->addExtension(new ReferenceExtension());
    }

    #[Override]
    public function parse(string $content, ?FilePath $path = null): Document {
        $node     = $this->parser->parse($content);
        $document = new Document($this, $node, $path);

        return $document;
    }

    #[Override]
    public function render(Document $document): string {
        return (string) $this->renderer->renderDocument($document->node);
    }
}
