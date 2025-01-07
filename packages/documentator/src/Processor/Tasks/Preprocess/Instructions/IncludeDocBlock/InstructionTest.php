<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Reference\Node;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Nop;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock\Exceptions\TargetIsNotValidPhpFile;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function trim;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    use WithProcessor;

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
        $context  = new Context($file, Mockery::mock(Document::class), new Node(), new Nop());
        $instance = $this->app()->make(Instruction::class);

        if ($expected instanceof Closure) {
            self::expectExceptionObject($expected($this, $context, $params));
        } else {
            $expected = trim(self::getTestData()->content($expected));
        }

        $actual = $this->getProcessorResult($fs, ($instance)($context, $params));

        if ($params->summary && $params->description) {
            self::assertInstanceOf(Document::class, $actual);
        } else {
            self::assertIsString($actual);
        }

        self::assertSame($expected, trim((string) $actual));
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
                'ValidExpected.txt',
                'Valid.txt',
                new Parameters('Valid.txt'),
            ],
            'with summary'     => [
                'ValidWithSummaryExpected.txt',
                'Valid.txt',
                new Parameters('Valid.txt', summary: true),
            ],
            'only summary'     => [
                'ValidOnlySummaryExpected.txt',
                'Valid.txt',
                new Parameters('Valid.txt', summary: true, description: false),
            ],
            'only description' => [
                'ValidOnlyDescriptionExpected.txt',
                'Valid.txt',
                new Parameters('Valid.txt', summary: false, description: true),
            ],
            'no docblock'      => [
                'NoDocBlockExpected.txt',
                'NoDocBlock.txt',
                new Parameters('NoDocBlock.txt'),
            ],
            'invalid'          => [
                static function (self $test, Context $context, Parameters $parameters): Exception {
                    return new TargetIsNotValidPhpFile($context, $parameters);
                },
                'Invalid.txt',
                new Parameters('Invalid.txt'),
            ],
        ];
    }
    // </editor-fold>
}
