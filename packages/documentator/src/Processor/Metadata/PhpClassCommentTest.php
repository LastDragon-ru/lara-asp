<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PhpDocBlock::class)]
final class PhpClassCommentTest extends TestCase {
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
            false,
        );
        $factory  = new PhpClassComment(new PhpClass());
        $metadata = $factory($file);

        self::assertNotNull($metadata);
        self::assertEquals(
            <<<'MARKDOWN'
            Description.

            Summary {@see stdClass} and {@see PhpClass}, {@see https://example.com/}.
            MARKDOWN,
            $metadata->comment->getText(),
        );
    }

    public function testInvokeNotPhp(): void {
        $file     = new File((new FilePath(__FILE__))->getNormalizedPath(), false);
        $factory  = new PhpClassComment(
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
