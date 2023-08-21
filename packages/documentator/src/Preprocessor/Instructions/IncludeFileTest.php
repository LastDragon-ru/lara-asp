<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;
use function dirname;

/**
 * @internal
 */
#[CoversClass(IncludeFile::class)]
class IncludeFileTest extends TestCase {
    public function testProcessRelative(): void {
        $path     = dirname(self::getTestData()->path('.md'));
        $file     = basename(self::getTestData()->path('.md'));
        $expected = self::getTestData()->content('.md');
        $instance = $this->app->make(IncludeFile::class);

        self::assertEquals($expected, $instance->process($path, $file));
    }

    public function testProcessAbsolute(): void {
        $path     = 'invalid/directory';
        $file     = self::getTestData()->path('.md');
        $expected = self::getTestData()->content('.md');
        $instance = $this->app->make(IncludeFile::class);

        self::assertEquals($expected, $instance->process($path, $file));
    }
}
