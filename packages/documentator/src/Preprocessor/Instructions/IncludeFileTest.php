<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(IncludeFile::class)]
class IncludeFileTest extends TestCase {
    public function testProcessRelative(): void {
        $file     = self::getTestData()->file('.md');
        $instance = $this->app->make(IncludeFile::class);
        $expected = self::getTestData()->content('.md');

        self::assertEquals($expected, $instance->process($file->getPathname(), $file->getFilename()));
    }

    public function testProcessAbsolute(): void {
        $path     = 'invalid/directory';
        $file     = self::getTestData()->path('.md');
        $instance = $this->app->make(IncludeFile::class);
        $expected = self::getTestData()->content('.md');

        self::assertEquals($expected, $instance->process($path, $file));
    }
}
