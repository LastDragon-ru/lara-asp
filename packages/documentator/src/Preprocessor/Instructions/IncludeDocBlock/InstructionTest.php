<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocBlock;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotValidPhpFile;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function file_get_contents;
use function str_replace;

/**
 * @internal
 */
#[CoversClass(Instruction::class)]
final class InstructionTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderProcess')]
    public function testProcess(Exception|string $expected, string $file, Parameters $params): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        } else {
            $expected = self::getTestData()->content($expected);
        }

        $file     = self::getTestData()->file($file);
        $target   = (string) file_get_contents($file->getPathname());
        $context  = new Context($file->getPathname(), $file->getFilename(), null);
        $instance = $this->app()->make(Instruction::class);

        self::assertEquals($expected, $instance->process($context, $target, $params));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|string, string, Parameters}>
     */
    public static function dataProviderProcess(): array {
        return [
            'default'      => [
                'ValidExpected.txt',
                'Valid.txt',
                new Parameters(),
            ],
            'with summary' => [
                'ValidWithSummaryExpected.txt',
                'Valid.txt',
                new Parameters(summary: true),
            ],
            'only summary' => [
                'ValidOnlySummaryExpected.txt',
                'Valid.txt',
                new Parameters(summary: true, description: false),
            ],
            'no docblock'  => [
                'NoDocBlockExpected.txt',
                'NoDocBlock.txt',
                new Parameters(),
            ],
            'invalid'      => [
                new TargetIsNotValidPhpFile(
                    new Context(
                        str_replace('\\', '/', __DIR__.'/InstructionTest/Invalid.txt'),
                        'Invalid.txt',
                        null,
                    ),
                ),
                'Invalid.txt',
                new Parameters(),
            ],
        ];
    }
    // </editor-fold>
}
