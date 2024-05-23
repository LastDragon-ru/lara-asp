<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;
use function dirname;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testInvokeSameDirectory(): void {
        $path     = self::getTestData()->path('Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters();
        $context  = new Context($root, $root, $file, './', '');
        $instance = $this->app()->make(Instruction::class);
        $actual   = ($instance)($context, $root->getPath(), $params);

        self::assertEquals(
            self::getTestData()->content('~SameDirectory.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testInvokeAnotherDirectory(): void {
        $path     = self::getTestData()->path('~AnotherDirectory.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters();
        $context  = new Context($root, $root, $file, basename(self::getTestData()->path('/')), '');
        $instance = $this->app()->make(Instruction::class);
        $target   = Path::join($root->getPath(), $context->target);
        $actual   = ($instance)($context, $target, $params);

        self::assertEquals(
            self::getTestData()->content('~AnotherDirectory.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testInvokeNestedDirectories(): void {
        $path     = self::getTestData()->path('nested/Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters(null);
        $context  = new Context($root, $root, $file, './', '');
        $instance = $this->app()->make(Instruction::class);
        $actual   = ($instance)($context, $root->getPath(), $params);

        self::assertEquals(
            self::getTestData()->content('~NestedDirectories.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testInvokeWithoutTitle(): void {
        $path     = self::getTestData()->path('invalid/Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters();
        $context  = new Context($root, $root, $file, './', '');
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new DocumentTitleIsMissing($context, 'WithoutTitle.md'),
        );

        ($instance)($context, $root->getPath(), $params);
    }
}
