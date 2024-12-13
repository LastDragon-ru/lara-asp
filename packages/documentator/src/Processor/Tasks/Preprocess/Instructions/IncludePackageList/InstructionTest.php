<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludePackageList\Exceptions\PackageReadmeIsEmpty;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
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
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderProcess')]
    public function testInvoke(string $expected, string $template, SortOrder $order): void {
        $path     = (new FilePath(self::getTestData()->path('Document.md')))->getNormalizedPath();
        $root     = new Directory($path->getDirectoryPath());
        $file     = new File($path);
        $target   = $root->getDirectoryPath('packages');
        $params   = new Parameters('...', template: $template, order: $order);
        $context  = new Context($root, $file, Mockery::mock(Document::class), new Node(), new Nop());
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
        $path     = (new FilePath(self::getTestData()->path('Document.md')))->getNormalizedPath();
        $fs       = new FileSystem(new Directory($path->getDirectoryPath()));
        $file     = new File($path);
        $target   = $fs->input->getDirectoryPath('no readme');
        $params   = new Parameters('...');
        $context  = new Context($fs->input, $file, Mockery::mock(Document::class), new Node(), new Nop());
        $instance = $this->app()->make(Instruction::class);
        $package  = $fs->getDirectory(new Directory($target), 'package');

        self::assertNotNull($package);
        self::expectExceptionObject(
            new DependencyNotFound($context->root, $context->file, new FileReference('no readme/package/README.md')),
        );

        ProcessorHelper::runInstruction($instance, $context, $target, $params);
    }

    public function testInvokeEmptyReadme(): void {
        $path     = (new FilePath(self::getTestData()->path('Document.md')))->getNormalizedPath();
        $fs       = new FileSystem(new Directory($path->getDirectoryPath()));
        $file     = new File($path);
        $target   = $fs->input->getDirectoryPath('empty readme');
        $params   = new Parameters('...');
        $context  = new Context($fs->input, $file, Mockery::mock(Document::class), new Node(), new Nop());
        $instance = $this->app()->make(Instruction::class);
        $package  = $fs->getDirectory(new Directory($target), 'package');
        $expected = $fs->getFile($fs->input, 'empty readme/package/README.md');

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
