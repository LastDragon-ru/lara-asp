<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocBlock;

use Exception;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotValidPhpFile;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function str_replace;

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
    public function testProcess(Exception|string $expected, string $file, Parameters $params): void {
        $file     = self::getTestData()->file($file);
        $instance = Container::getInstance()->make(Instruction::class);

        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        } else {
            $expected = self::getTestData()->content($expected);
        }

        self::assertEquals($expected, $instance->process($file->getPathname(), $file->getFilename(), $params));
    }

    public function testProcessAbsolute(): void {
        $path     = 'invalid/directory';
        $file     = self::getTestData()->path('Valid.txt');
        $params   = new Parameters();
        $instance = Container::getInstance()->make(Instruction::class);
        $expected = self::getTestData()->content('ValidExpected.txt');

        self::assertEquals($expected, $instance->process($path, $file, $params));
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
                    str_replace('\\', '/', __DIR__.'/InstructionTest/Invalid.txt'),
                    'Invalid.txt',
                ),
                'Invalid.txt',
                new Parameters(),
            ],
        ];
    }
    // </editor-fold>
}
