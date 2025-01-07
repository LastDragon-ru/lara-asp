<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

use Exception;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Exceptions\PackageReadmeIsEmpty;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @deprecated %{VERSION}
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    use WithProcessor;

    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderProcess')]
    public function testInvoke(string $expected, string $template, SortOrder $order): void {
        $path     = (new FilePath(self::getTestData()->path('Document.md')))->getNormalizedPath();
        $fs       = $this->getFileSystem($path->getDirectoryPath());
        $file     = $fs->getFile($path);
        $params   = new Parameters('packages', template: $template, order: $order);
        $context  = new Context($file, Mockery::mock(Document::class), new Node(), new Nop());
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
        $context  = new Context($file, Mockery::mock(Document::class), new Node(), new Nop());
        $instance = $this->app()->make(Instruction::class);

        self::expectExceptionObject(
            new DependencyUnresolvable(
                new FileReference($fs->input->getFilePath('no readme/package/README.md')),
                new Exception(),
            ),
        );

        $this->getProcessorResult($fs, ($instance)($context, $params));
    }

    public function testInvokeEmptyReadme(): void {
        $path     = (new FilePath(self::getTestData()->path('Document.md')))->getNormalizedPath();
        $fs       = $this->getFileSystem($path->getDirectoryPath());
        $file     = $fs->getFile($path);
        $target   = $fs->input->getDirectoryPath('empty readme');
        $params   = new Parameters((string) $target);
        $context  = new Context($file, Mockery::mock(Document::class), new Node(), new Nop());
        $instance = $this->app()->make(Instruction::class);
        $package  = $fs->getDirectory($target->getDirectoryPath('package'));

        self::expectExceptionObject(
            new PackageReadmeIsEmpty($context, $params, $package->getName()),
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
