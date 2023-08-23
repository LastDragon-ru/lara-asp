<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;

/**
 * @internal
 */
#[CoversClass(IncludePackageList::class)]
class IncludePackageListTest extends TestCase {
    public function testProcess(): void {
        $path     = self::getTestData()->file('.md');
        $instance = $this->app->make(IncludePackageList::class);
        $actual   = $instance->process($path->getPathname(), basename(self::getTestData()->path('/')));

        self::assertEquals(
            self::getTestData()->content('.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }
}
