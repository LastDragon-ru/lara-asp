<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList;

// @phpcs:disable Generic.Files.LineLength.TooLong

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList\Exceptions\PackageComposerJsonIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList\Exceptions\PackageReadmeIsEmpty;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList\Exceptions\PackageReadmeTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileDependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

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
        $path     = Path::normalize(self::getTestData()->path('Document.md'));
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $target   = new Directory($root->getPath('packages'), false);
        $params   = new Parameters(template: $template);
        $context  = new Context($root, $file, $target->getPath(), '');
        $instance = $this->app()->make(Instruction::class);
        $actual   = ProcessorHelper::runInstruction($instance, $context, $target, $params);

        self::assertEquals(
            self::getTestData()->content($expected),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testInvokeNotAPackage(): void {
        $path     = Path::normalize(self::getTestData()->path('Document.md'));
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $target   = new Directory($root->getPath('invalid'), false);
        $params   = new Parameters();
        $context  = new Context($root, $file, $target->getPath(), '');
        $instance = $this->app()->make(Instruction::class);
        $package  = $target->getDirectory('package');

        self::assertNotNull($package);
        self::expectExceptionObject(
            new PackageComposerJsonIsMissing($context, $package),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeNoReadme(): void {
        $path     = self::getTestData()->path('Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $target   = new Directory($root->getPath('no readme'), false);
        $params   = new Parameters();
        $context  = new Context($root, $file, $target->getPath(), '');
        $instance = $this->app()->make(Instruction::class);
        $package  = $target->getDirectory('package');

        self::assertNotNull($package);
        self::expectExceptionObject(
            new FileDependencyNotFound($context->root, $context->file, 'no readme/package/README.md'),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeEmptyReadme(): void {
        $path     = self::getTestData()->path('Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $target   = new Directory($root->getPath('empty readme'), false);
        $params   = new Parameters();
        $context  = new Context($root, $file, $target->getPath(), '');
        $instance = $this->app()->make(Instruction::class);
        $package  = $target->getDirectory('package');
        $expected = $root->getFile('empty readme/package/README.md');

        self::assertNotNull($package);
        self::assertNotNull($expected);
        self::expectExceptionObject(
            new PackageReadmeIsEmpty($context, $package, $expected),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeNoTitle(): void {
        $path     = self::getTestData()->path('Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $target   = new Directory($root->getPath('no title'), false);
        $params   = new Parameters();
        $context  = new Context($root, $file, $target->getPath(), '');
        $instance = $this->app()->make(Instruction::class);
        $package  = $target->getDirectory('package');
        $expected = $root->getFile('no title/package/README.md');

        self::assertNotNull($package);
        self::assertNotNull($expected);
        self::expectExceptionObject(
            new PackageReadmeTitleIsMissing($context, $package, $expected),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
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
