<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithPreprocess;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function mb_trim;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    use WithPreprocess;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param Closure(self, Context, Parameters): Exception|string $expected
     */
    #[DataProvider('dataProviderProcess')]
    public function testInvoke(Closure|string $expected, string $file, Parameters $params): void {
        $path     = (new FilePath(self::getTestData()->path($file)))->getNormalizedPath();
        $fs       = $this->getFileSystem($path->getDirectoryPath());
        $file     = $fs->getFile($path);
        $context  = $this->getPreprocessInstructionContext($fs, $file);
        $instance = $this->app()->make(Instruction::class);

        if ($expected instanceof Closure) {
            self::expectExceptionObject($expected($this, $context, $params));
        } else {
            $expected = mb_trim(self::getTestData()->content($expected));
        }

        $actual = ($instance)($context, $params);

        self::assertInstanceOf(Document::class, $actual);
        self::assertSame($expected, mb_trim((string) $actual));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Closure(self, Context, Parameters): Exception|string, string, Parameters}>
     */
    public static function dataProviderProcess(): array {
        return [
            'default'          => [
                'ValidExpected.md',
                'Valid.php',
                new Parameters('Valid.php'),
            ],
            'with summary'     => [
                'ValidWithSummaryExpected.md',
                'Valid.php',
                new Parameters('Valid.php', summary: true),
            ],
            'only summary'     => [
                'ValidOnlySummaryExpected.md',
                'Valid.php',
                new Parameters('Valid.php', summary: true, description: false),
            ],
            'only description' => [
                'ValidOnlyDescriptionExpected.md',
                'Valid.php',
                new Parameters('Valid.php', summary: false, description: true),
            ],
            'no docblock'      => [
                'NoDocBlockExpected.md',
                'NoDocBlock.php',
                new Parameters('NoDocBlock.php'),
            ],
        ];
    }
    // </editor-fold>
}
