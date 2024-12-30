<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PhpClassMarkdown::class)]
final class PhpClassMarkdownTest extends TestCase {
    use WithProcessor;

    public function testInvoke(): void {
        $content  = <<<'PHP'
        <?php declare(strict_types = 1);

        namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

        use stdClass;
        use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClass;

        /**
         * Description.
         *
         * Summary {@see stdClass} and {@see PhpClass}, {@see https://example.com/}.
         */
        class A {
            // empty
        }
        PHP;
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(self::getTempFile($content)->getPathname());
        $factory  = new PhpClassMarkdown(
            $this->app()->make(PhpDocumentFactory::class),
        );
        $metadata = $factory($file);

        self::assertNotNull($metadata);
        self::assertEquals(
            <<<'MARKDOWN'
            Description.

            Summary `\stdClass` and `\LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClass`, {@see https://example.com/}.

            MARKDOWN,
            (string) $metadata,
        );
    }

    public function testInvokeEmpty(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(__FILE__);
        $factory  = new PhpClassMarkdown(
            $this->app()->make(PhpDocumentFactory::class),
        );
        $metadata = $factory($file);

        self::assertNotNull($metadata);
        self::assertTrue($metadata->isEmpty());
    }

    public function testInvokeNotPhp(): void {
        $fs      = $this->getFileSystem(__DIR__);
        $file    = $fs->getFile(__FILE__);
        $factory = new PhpClassMarkdown(
            $this->app()->make(PhpDocumentFactory::class),
        );

        $this->override(
            PhpClassComment::class,
            new class() extends PhpClassComment {
                #[Override]
                public function __invoke(File $file): mixed {
                    return null;
                }
            },
        );

        $metadata = $factory($file);

        self::assertNull($metadata);
    }
}
