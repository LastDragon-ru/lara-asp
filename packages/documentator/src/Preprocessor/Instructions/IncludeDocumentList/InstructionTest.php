<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocumentList;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
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
        $instance = Container::getInstance()->make(Instruction::class);
        $actual   = $instance->process($path->getPathname(), './', $params);

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
        $instance = Container::getInstance()->make(Instruction::class);
        $actual   = $instance->process($path->getPathname(), basename(self::getTestData()->path('/')), $params);

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
        $instance = Container::getInstance()->make(Instruction::class);
        $actual   = $instance->process($path->getPathname(), './', $params);

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
        $instance = Container::getInstance()->make(Instruction::class);

        self::expectExceptionObject(
            new DocumentTitleIsMissing(
                $path->getPathname(),
                $target,
                'WithoutTitle.md',
            ),
        );

        $instance->process($path->getPathname(), $target, $params);
    }
}
