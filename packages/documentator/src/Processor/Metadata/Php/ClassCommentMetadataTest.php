<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
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

        use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php\ClassObject;
        use stdClass;

        /**
         * Description.
         *
         * Summary {@see stdClass} and {@see ClassObject}, {@see https://example.com/}.
         */
        class A {
            // empty
        }
        PHP;
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(self::getTempFile($content, '.php')->getPathname());
        $metadata = new ClassCommentMetadata();
        $resolved = $metadata->resolve($file, ClassComment::class);

        self::assertNotNull($resolved);
        self::assertSame(
            <<<'MARKDOWN'
            Description.

            Summary {@see stdClass} and {@see ClassObject}, {@see https://example.com/}.
            MARKDOWN,
            $resolved->comment->getText(),
        );
    }
}
