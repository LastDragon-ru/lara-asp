<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

use Exception;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithPreprocess;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @deprecated 8.0.0
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    use WithPreprocess;

    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderProcess')]
    public function testInvoke(string $expected, string $template, SortOrder $order): void {
        $path     = (new FilePath(self::getTestData()->path('Document.md')))->getNormalizedPath();
        $fs       = $this->getFileSystem($path->getDirectoryPath());
        $file     = $fs->getFile($path);
        $params   = new Parameters('packages', template: $template, order: $order);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);
        $actual   = $this->getProcessorResult($fs, ($instance)($context, $params));

        self::assertSame(
            self::getTestData()->content($expected),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testInvokeNoReadme(): void {
        $path     = (new FilePath(self::getTestData()->path('Document.md')))->getNormalizedPath();
        $fs       = $this->getFileSystem($path->getDirectoryPath());
        $file     = $fs->getFile($path);
        $target   = $fs->input->getDirectoryPath('no readme');
        $params   = new Parameters((string) $target);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new DependencyUnresolvable(
                new Exception(),
            ),
        );

        $this->getProcessorResult($fs, ($instance)($context, $params));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, ?string, SortOrder}>
     */
    public static function dataProviderProcess(): array {
        return [
            'default-asc'  => ['~DefaultAsc.md', 'default', SortOrder::Asc],
            'default-desc' => ['~DefaultDesc.md', 'default', SortOrder::Desc],
            'upgradable'   => ['~Upgradable.md', 'upgradable', SortOrder::Asc],
        ];
    }
    // </editor-fold>
}
