<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Utils\PhpDocumentFactory;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;

/**
 * @internal
 */
#[CoversClass(ClassMarkdownCast::class)]
final class ClassMarkdownCastTest extends TestCase {
    use WithProcessor;

    public function testCastTo(): void {
        $content = <<<'PHP'
        <?php declare(strict_types = 1);

        namespace LastDragon_ru\LaraASP\Documentator\Processor\Cast;

        use stdClass;
        use LastDragon_ru\LaraASP\Documentator\Processor\Casts\PhpClass;

        /**
         * Description.
         *
         * Summary {@see stdClass} and {@see PhpClass}, {@see https://example.com/}.
         */
        class A {
            // empty
        }
        PHP;
        $path    = self::getTempFile($content, '.php')->getPathname();
        $fs      = $this->getFileSystem(dirname($path));

        self::assertNotEmpty($path);

        $file  = $fs->get($fs->input->file($path));
        $cast  = new ClassMarkdownCast($this->app()->make(PhpDocumentFactory::class));
        $value = $cast->castTo($file, Document::class);

        self::assertSame(
            <<<'MARKDOWN'
            Description.

            Summary `\stdClass` and `\LastDragon_ru\LaraASP\Documentator\Processor\Casts\PhpClass`, {@see https://example.com/}.
            MARKDOWN,
            (string) $value,
        );
    }
}
