<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;

/**
 * @internal
 */
#[CoversClass(ClassCommentCast::class)]
final class ClassCommentCastTest extends TestCase {
    use WithProcessor;

    public function testCastTo(): void {
        $content = <<<'PHP'
        <?php declare(strict_types = 1);

        namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

        use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ClassObject;
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
        $path    = self::getTempFile($content, '.php')->getPathname();
        $fs      = $this->getFileSystem(dirname($path));

        self::assertNotEmpty($path);

        $file  = $fs->get($fs->input->file($path));
        $cast  = new ClassCommentCast();
        $value = $cast->castTo($file, ClassComment::class);

        self::assertNotNull($value);
        self::assertSame(
            <<<'MARKDOWN'
            Description.

            Summary {@see stdClass} and {@see ClassObject}, {@see https://example.com/}.
            MARKDOWN,
            $value->comment->getText(),
        );
    }
}
