<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @deprecated 7.0.0
 * @internal
 */
#[CoversClass(PhpDocBlock::class)]
final class PhpDocBlockTest extends TestCase {
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
        $factory  = new PhpDocBlock(
            $this->app()->make(Markdown::class),
            $this->app()->make(LinkFactory::class),
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
        $factory  = new PhpDocBlock(
            $this->app()->make(Markdown::class),
            $this->app()->make(LinkFactory::class),
        );
        $metadata = $factory($file);

        self::assertNotNull($metadata);
        self::assertTrue($metadata->isEmpty());
    }

    public function testInvokeNotPhp(): void {
        $fs      = $this->getFileSystem(__DIR__);
        $file    = $fs->getFile(__FILE__);
        $factory = new PhpDocBlock(
            $this->app()->make(Markdown::class),
            $this->app()->make(LinkFactory::class),
        );

        $this->override(
            PhpClass::class,
            new class() extends PhpClass {
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
