<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown as MarkdownContract;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Parser\MarkdownParserInterface;
use Override;

class Markdown implements MarkdownContract {
    protected readonly EnvironmentInterface    $environment;
    protected readonly MarkdownParserInterface $parser;

    public function __construct() {
        $this->environment = $this->initialize();
        $this->parser      = new MarkdownParser($this->environment);
    }

    protected function initialize(): Environment {
        return (new GithubFlavoredMarkdownConverter())->getEnvironment()
            ->addExtension(new Extension());
    }

    #[Override]
    public function parse(string $content, ?FilePath $path = null): Document {
        $node     = $this->parser->parse($content);
        $document = new Document($this, $node, $path);

        return $document;
    }
}
