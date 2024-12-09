<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock\Exceptions\TargetIsNotValidPhpFile;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\ProcessorHelper;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function trim;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param Closure(self, Context): Exception|string $expected
     */
    #[DataProvider('dataProviderProcess')]
    public function testInvoke(Closure|string $expected, string $file, Parameters $params): void {
        $path     = (new FilePath(self::getTestData()->path($file)))->getNormalizedPath();
        $root     = new Directory($path->getDirectoryPath(), false);
        $file     = new File($path, false);
        $target   = $file->getName();
        $context  = new Context($root, $file, Mockery::mock(Document::class), new Block(), new Nop());
        $instance = $this->app()->make(Instruction::class);

        if ($expected instanceof Closure) {
            self::expectExceptionObject($expected($this, $context));
        } else {
            $expected = trim(self::getTestData()->content($expected));
        }

        $actual = ProcessorHelper::runInstruction($instance, $context, $target, $params);

        if ($params->summary && $params->description) {
            self::assertInstanceOf(Document::class, $actual);
        } else {
            self::assertIsString($actual);
        }

        self::assertEquals($expected, trim((string) $actual));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Closure(self, Context): Exception|string, string, Parameters}>
     */
    public static function dataProviderProcess(): array {
        return [
            'default'          => [
                'ValidExpected.txt',
                'Valid.txt',
                new Parameters('...'),
            ],
            'with summary'     => [
                'ValidWithSummaryExpected.txt',
                'Valid.txt',
                new Parameters('...', summary: true),
            ],
            'only summary'     => [
                'ValidOnlySummaryExpected.txt',
                'Valid.txt',
                new Parameters('...', summary: true, description: false),
            ],
            'only description' => [
                'ValidOnlyDescriptionExpected.txt',
                'Valid.txt',
                new Parameters('...', summary: false, description: true),
            ],
            'no docblock'      => [
                'NoDocBlockExpected.txt',
                'NoDocBlock.txt',
                new Parameters('...'),
            ],
            'invalid'          => [
                static function (self $test, Context $context): Exception {
                    return new TargetIsNotValidPhpFile($context);
                },
                'Invalid.txt',
                new Parameters('...'),
            ],
        ];
    }
    // </editor-fold>
}
