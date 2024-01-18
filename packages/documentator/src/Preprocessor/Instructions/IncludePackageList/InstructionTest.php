<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludePackageList;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageComposerJsonIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageReadmeIsMissing;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderProcess
     */
    public function testProcess(string $expected, string $template): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/packages'));
        $params   = new Parameters(template: $template);
        $instance = Container::getInstance()->make(Instruction::class);
        $actual   = $instance->process($path, $target, $params);

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
        $instance = Container::getInstance()->make(Instruction::class);

        self::expectExceptionObject(
            new PackageComposerJsonIsMissing(
                $path,
                $target,
                'invalid/package',
            ),
        );

        $instance->process($path, $target, $params);
    }

    public function testProcessNoReadme(): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/no readme'));
        $params   = new Parameters();
        $instance = Container::getInstance()->make(Instruction::class);

        self::expectExceptionObject(
            new PackageReadmeIsMissing(
                $path,
                $target,
                'no readme/package',
            ),
        );

        $instance->process($path, $target, $params);
    }

    public function testProcessNoTitle(): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/no title'));
        $params   = new Parameters();
        $instance = Container::getInstance()->make(Instruction::class);

        self::expectExceptionObject(
            new DocumentTitleIsMissing(
                $path,
                $target,
                'no title/package/README.md',
            ),
        );

        $instance->process($path, $target, $params);
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
