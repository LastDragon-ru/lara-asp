<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Testing\Package;

use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document as DocumentImpl;
use LastDragon_ru\LaraASP\Documentator\Markdown\Environment\Markdown as MarkdownImpl;
use LastDragon_ru\LaraASP\Documentator\Utils\Sorter;
use League\CommonMark\Environment\EnvironmentInterface;

/**
 * @phpstan-require-extends TestCase
 * @internal
 */
trait WithMarkdown {
    abstract protected function app(): Application;

    protected function assertMarkdownDocumentEquals(string $expected, Document $document): void {
        self::assertSame($expected, $this->getMarkdownDocumentRenderer($document)->render($document));
    }

    protected function getMarkdownDocumentRenderer(Markdown|Document $object): MarkdownDocumentRenderer {
        $env      = $this->getMarkdownDocumentEnvironment($object);
        $sorter   = $this->app()->make(Sorter::class);
        $renderer = new MarkdownDocumentRenderer($env, $sorter);

        return $renderer;
    }

    private function getMarkdownDocumentEnvironment(Markdown|Document $object): EnvironmentInterface {
        // Helpers
        $documentHelper = new class() extends DocumentImpl {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // parent isn't needed
            }

            public function getDocumentMarkdown(DocumentImpl $document): Markdown {
                return $document->markdown;
            }
        };
        $markdownHelper = new class() extends MarkdownImpl {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // parent isn't needed
            }

            public function getMarkdownEnvironment(Markdown $markdown): ?EnvironmentInterface {
                return $markdown instanceof MarkdownImpl ? $markdown->environment : null;
            }
        };

        // Detect
        $environment = null;

        if ($object instanceof DocumentImpl) {
            $object = $documentHelper->getDocumentMarkdown($object);
        }

        if ($object instanceof MarkdownImpl) {
            $environment = $markdownHelper->getMarkdownEnvironment($object);
        }

        self::assertNotNull($environment);

        return $environment;
    }
}
