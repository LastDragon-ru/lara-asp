<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PhpDocBlock::class)]
final class PhpClassCommentTest extends TestCase {
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
        $factory  = new PhpClassComment();
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
        $fs      = $this->getFileSystem(__DIR__);
        $file    = $fs->getFile(self::getTempFile()->getPathname());
        $factory = new PhpClassComment();

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
