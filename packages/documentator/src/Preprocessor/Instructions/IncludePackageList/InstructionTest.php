<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageComposerJsonIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageReadmeIsMissing;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function basename;
use function dirname;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderProcess')]
    public function testInvoke(string $expected, string $template): void {
        $path     = self::getTestData()->path('Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $target   = basename(self::getTestData()->path('/packages'));
        $params   = new Parameters(template: $template);
        $context  = new Context($root, $root, $file, $target, '');
        $resolved = Path::join($root->getPath(), $context->target);
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $resolved, $params);

        self::assertEquals(
            self::getTestData()->content($expected),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testInvokeNotAPackage(): void {
        $path     = self::getTestData()->path('Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $target   = basename(self::getTestData()->path('/invalid'));
        $params   = new Parameters();
        $context  = new Context($root, $root, $file, $target, '');
        $resolved = Path::join($root->getPath(), $context->target);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new PackageComposerJsonIsMissing($context, 'invalid/package'),
        );

        ProcessorHelper::runInstruction($instance, $context, $resolved, $params);
    }

    public function testInvokeNoReadme(): void {
        $path     = self::getTestData()->path('Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $target   = basename(self::getTestData()->path('/no readme'));
        $params   = new Parameters();
        $context  = new Context($root, $root, $file, $target, '');
        $resolved = Path::join($root->getPath(), $context->target);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new PackageReadmeIsMissing($context, 'no readme/package'),
        );

        ProcessorHelper::runInstruction($instance, $context, $resolved, $params);
    }

    public function testInvokeNoTitle(): void {
        $path     = self::getTestData()->path('Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File(Path::normalize(__FILE__), false);
        $target   = basename(self::getTestData()->path('/no title'));
        $params   = new Parameters();
        $context  = new Context($root, $root, $file, $target, '');
        $resolved = Path::join($root->getPath(), $context->target);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new DocumentTitleIsMissing($context, 'no title/package/README.md'),
        );

        ProcessorHelper::runInstruction($instance, $context, $resolved, $params);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, ?string}>
     */
    public static function dataProviderProcess(): array {
        return [
            'default'    => ['~default.md', 'default'],
            'upgradable' => ['~upgradable.md', 'upgradable'],
        ];
    }
    // </editor-fold>
}
