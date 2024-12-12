<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\LinkFactory;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @deprecated 7.0.0
 * @internal
 */
#[CoversClass(PhpDocBlock::class)]
final class PhpDocBlockTest extends TestCase {
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
        $file     = new File(
            (new FilePath(self::getTempFile($content)->getPathname()))->getNormalizedPath(),
        );
        $factory  = new PhpDocBlock(
            $this->app()->make(Markdown::class),
            $this->app()->make(LinkFactory::class),
            new PhpClass(),
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
        $file     = new File((new FilePath(__FILE__))->getNormalizedPath());
        $factory  = new PhpDocBlock(
            $this->app()->make(Markdown::class),
            $this->app()->make(LinkFactory::class),
            new PhpClass(),
        );
        $metadata = $factory($file);

        self::assertNotNull($metadata);
        self::assertTrue($metadata->isEmpty());
    }

    public function testInvokeNotPhp(): void {
        $file     = new File((new FilePath(__FILE__))->getNormalizedPath());
        $factory  = new PhpDocBlock(
            $this->app()->make(Markdown::class),
            $this->app()->make(LinkFactory::class),
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
