<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testInvokeSameDirectory(): void {
        $path     = (new FilePath(self::getTestData()->path('Document.md')))->getNormalizedPath();
        $root     = new Directory($path->getDirectoryPath(), false);
        $file     = new File($path, false);
        $params   = new Parameters('...');
        $target   = './';
        $context  = new Context($root, $file, $target, '{...}', new Nop());
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
        $path    = (new FilePath(self::getTestData()->path('~AnotherDirectory.md')))->getNormalizedPath();
        $root    = new Directory($path->getDirectoryPath(), false);
        $file    = new File($path, false);
        $params  = new Parameters('...');
        $target  = basename(self::getTestData()->path('/'));
        $context = new Context($root, $file, $target, '', new Nop());

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
        $path     = (new FilePath(self::getTestData()->path('nested/Document.md')))->getNormalizedPath();
        $root     = new Directory($path->getDirectoryPath(), false);
        $file     = new File($path, false);
        $params   = new Parameters('...', null, order: SortOrder::Desc);
        $target   = './';
        $context  = new Context($root, $file, $target, '', new Nop());
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
}
