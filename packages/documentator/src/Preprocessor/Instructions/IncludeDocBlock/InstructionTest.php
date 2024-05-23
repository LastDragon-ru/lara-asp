<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeDocBlock;

use Closure;
use Exception;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotValidPhpFile;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function dirname;

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
        $path     = self::getTestData()->path($file);
        $root     = new Directory(dirname($path), false);
        $file     = new File($path, false);
        $target   = $file;
        $context  = new Context($root, $root, $file, $file->getName(), null);
        $instance = $this->app()->make(Instruction::class);

        if ($expected instanceof Closure) {
            self::expectExceptionObject($expected($this, $context));
        } else {
            $expected = self::getTestData()->content($expected);
        }

        self::assertEquals($expected, ($instance)($context, $target, $params));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{Closure(self, Context): Exception|string, string, Parameters}>
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
                static function (self $test, Context $context): Exception {
                    return new TargetIsNotValidPhpFile($context);
                },
                'Invalid.txt',
                new Parameters(),
            ],
        ];
    }
    // </editor-fold>
}
