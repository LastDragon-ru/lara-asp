<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Exceptions\PackageReadmeIsEmpty;
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
        $target   = $root->getPath('packages');
        $params   = new Parameters('...', template: $template);
        $context  = new Context($root, $file, $target, '{...}');
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

    public function testInvokeNoReadme(): void {
        $fs       = new FileSystem();
        $path     = self::getTestData()->path('Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $target   = $root->getPath('no readme');
        $params   = new Parameters('...');
        $context  = new Context($root, $file, $target, '{...}');
        $instance = $this->app()->make(Instruction::class);
        $package  = $fs->getDirectory(new Directory($target, false), 'package');

        self::assertNotNull($package);
        self::expectExceptionObject(
            new DependencyNotFound($context->root, $context->file, new FileReference('no readme/package/README.md')),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeEmptyReadme(): void {
        $fs       = new FileSystem();
        $path     = self::getTestData()->path('Document.md');
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $target   = $root->getPath('empty readme');
        $params   = new Parameters('...');
        $context  = new Context($root, $file, $target, '{...}');
        $instance = $this->app()->make(Instruction::class);
        $package  = $fs->getDirectory(new Directory($target, false), 'package');
        $expected = $fs->getFile($root, 'empty readme/package/README.md');

        self::assertNotNull($package);
        self::assertNotNull($expected);
        self::expectExceptionObject(
            new PackageReadmeIsEmpty($context, $package, $expected),
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
