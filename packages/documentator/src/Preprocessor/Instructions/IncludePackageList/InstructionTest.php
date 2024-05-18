<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageComposerJsonIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageReadmeIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function basename;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderProcess')]
    public function testProcess(string $expected, string $template): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/packages'));
        $params   = new Parameters(template: $template);
        $context  = new Context($path, $target, '');
        $resolved = (new DirectoryPath())->resolve($context, null);
        $instance = $this->app()->make(Instruction::class);
        $actual   = $instance->process($context, $resolved, $params);

        self::assertEquals(
            self::getTestData()->content($expected),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testProcessNotAPackage(): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/invalid'));
        $params   = new Parameters();
        $context  = new Context($path, $target, '');
        $resolved = (new DirectoryPath())->resolve($context, null);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new PackageComposerJsonIsMissing($context, 'invalid/package'),
        );

        $instance->process($context, $resolved, $params);
    }

    public function testProcessNoReadme(): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/no readme'));
        $params   = new Parameters();
        $context  = new Context($path, $target, '');
        $resolved = (new DirectoryPath())->resolve($context, null);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new PackageReadmeIsMissing($context, 'no readme/package'),
        );

        $instance->process($context, $resolved, $params);
    }

    public function testProcessNoTitle(): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/no title'));
        $params   = new Parameters();
        $context  = new Context($path, $target, '');
        $resolved = (new DirectoryPath())->resolve($context, null);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new DocumentTitleIsMissing($context, 'no title/package/README.md'),
        );

        $instance->process($context, $resolved, $params);
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
