<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use Exception;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotValidPhpFile;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(IncludeDocBlock::class)]
class IncludeDocBlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderProcess
     */
    public function testProcess(Exception|string $expected, string $file, IncludeDocBlockParameters $params): void {
        $file     = self::getTestData()->file($file);
        $instance = Container::getInstance()->make(IncludeDocBlock::class);

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
        $params   = new IncludeDocBlockParameters();
        $instance = Container::getInstance()->make(IncludeDocBlock::class);
        $expected = self::getTestData()->content('ValidExpected.txt');

        self::assertEquals($expected, $instance->process($path, $file, $params));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Exception|string, string, IncludeDocBlockParameters}>
     */
    public static function dataProviderProcess(): array {
        return [
            'default'      => [
                'ValidExpected.txt',
                'Valid.txt',
                new IncludeDocBlockParameters(),
            ],
            'with summary' => [
                'ValidWithSummaryExpected.txt',
                'Valid.txt',
                new IncludeDocBlockParameters(summary: true),
            ],
            'only summary' => [
                'ValidOnlySummaryExpected.txt',
                'Valid.txt',
                new IncludeDocBlockParameters(summary: true, description: false),
            ],
            'no docblock'  => [
                'NoDocblockExpected.txt',
                'NoDocblock.txt',
                new IncludeDocBlockParameters(),
            ],
            'invalid'      => [
                new TargetIsNotValidPhpFile(__DIR__.'/IncludeDocBlockTest/Invalid.txt', 'Invalid.txt'),
                'Invalid.txt',
                new IncludeDocBlockParameters(),
            ],
        ];
    }
    // </editor-fold>
}
