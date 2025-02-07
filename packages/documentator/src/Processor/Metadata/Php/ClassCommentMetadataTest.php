<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ClassCommentMetadata::class)]
final class ClassCommentMetadataTest extends TestCase {
    use WithProcessor;

    public function testResolve(): void {
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
        $metadata = new ClassCommentMetadata();
        $resolved = $metadata->resolve($file, ClassComment::class);

        self::assertSame(
            <<<'MARKDOWN'
            Description.

            Summary {@see stdClass} and {@see PhpClass}, {@see https://example.com/}.
            MARKDOWN,
            $resolved->comment->getText(),
        );
    }
}
