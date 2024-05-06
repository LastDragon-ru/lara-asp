<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    public function testProcessSameDirectory(): void {
        $path     = self::getTestData()->file('Document.md');
        $params   = new Parameters();
        $context  = new Context($path->getPathname(), './', '');
        $instance = $this->app()->make(Instruction::class);
        $target   = (new DirectoryPath())->resolve($context, $params);
        $actual   = $instance->process($context, $target, $params);

        self::assertEquals(
            self::getTestData()->content('~SameDirectory.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testProcessAnotherDirectory(): void {
        $path     = self::getTestData()->file('~AnotherDirectory.md');
        $params   = new Parameters();
        $context  = new Context($path->getPathname(), basename(self::getTestData()->path('/')), '');
        $instance = $this->app()->make(Instruction::class);
        $target   = (new DirectoryPath())->resolve($context, $params);
        $actual   = $instance->process($context, $target, $params);

        self::assertEquals(
            self::getTestData()->content('~AnotherDirectory.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testProcessNestedDirectories(): void {
        $path     = self::getTestData()->file('nested/Document.md');
        $params   = new Parameters(null);
        $context  = new Context($path->getPathname(), './', '');
        $instance = $this->app()->make(Instruction::class);
        $target   = (new DirectoryPath())->resolve($context, $params);
        $actual   = $instance->process($context, $target, $params);

        self::assertEquals(
            self::getTestData()->content('~NestedDirectories.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testProcessWithoutTitle(): void {
        $path     = self::getTestData()->file('invalid/Document.md');
        $target   = './';
        $params   = new Parameters();
        $context  = new Context($path->getPathname(), './', '');
        $instance = $this->app()->make(Instruction::class);
        $target   = (new DirectoryPath())->resolve($context, $params);

        self::expectExceptionObject(
            new DocumentTitleIsMissing(
                $path->getPathname(),
                $target,
                'WithoutTitle.md',
            ),
        );

        $instance->process($context, $target, $params);
    }
}
