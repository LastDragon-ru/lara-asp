<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PhpClassMarkdown::class)]
final class PhpClassMarkdownTest extends TestCase {
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
            Path::normalize(self::getTempFile($content)->getPathname()),
            false,
        );
        $factory  = new PhpClassMarkdown(new PhpClassComment(new PhpClass()));
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
        $file     = new File(Path::normalize(__FILE__), false);
        $factory  = new PhpClassMarkdown(new PhpClassComment(new PhpClass()));
        $metadata = $factory($file);

        self::assertNotNull($metadata);
        self::assertTrue($metadata->isEmpty());
    }

    public function testInvokeNotPhp(): void {
        $file     = new File(Path::normalize(__FILE__), false);
        $factory  = new PhpClassMarkdown(
            new class(new PhpClass()) extends PhpClassComment {
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
