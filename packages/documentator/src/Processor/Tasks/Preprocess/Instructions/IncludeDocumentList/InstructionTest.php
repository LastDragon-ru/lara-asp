<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
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
        $params   = new Parameters('...');
        $target   = './';
        $context  = new Context($root, $file, $target, '{...}');
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $target, $params);

        self::assertEquals(
            self::getTestData()->content('~SameDirectory.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testInvokeAnotherDirectory(): void {
        $path    = self::getTestData()->path('~AnotherDirectory.md');
        $root    = new Directory(dirname($path), false);
        $file    = new File($path, false);
        $params  = new Parameters('...');
        $target  = basename(self::getTestData()->path('/'));
        $context = new Context($root, $file, $target, '');

        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $target, $params);

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
        $params   = new Parameters('...', null);
        $target   = './';
        $context  = new Context($root, $file, $target, '');
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $target, $params);

        self::assertEquals(
            self::getTestData()->content('~NestedDirectories.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testInvokeWithoutTitle(): void {
        $fs       = new FileSystem();
        $path     = self::getTestData()->path('invalid/Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $params   = new Parameters('...');
        $target   = './';
        $context  = new Context($root, $file, $target, '');
        $instance = $this->app()->make(Instruction::class);
        $expected = $fs->getFile($root, 'WithoutTitle.md');

        self::assertNotNull($expected);
        self::expectExceptionObject(
            new DocumentTitleIsMissing($context, $expected),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }
}
