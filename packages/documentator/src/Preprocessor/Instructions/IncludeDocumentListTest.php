<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;

/**
 * @internal
 */
#[CoversClass(IncludeDocumentList::class)]
class IncludeDocumentListTest extends TestCase {
    public function testProcessSameDirectory(): void {
        $path     = self::getTestData()->file('Document.md');
        $instance = $this->app->make(IncludeDocumentList::class);
        $actual   = $instance->process($path->getPathname(), './');

        self::assertEquals(
            self::getTestData()->content('~SameDirectory.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testProcessAnotherDirectory(): void {
        $path     = self::getTestData()->file('~AnotherDirectory.md');
        $instance = $this->app->make(IncludeDocumentList::class);
        $actual   = $instance->process($path->getPathname(), basename(self::getTestData()->path('/')));

        self::assertEquals(
            self::getTestData()->content('~AnotherDirectory.md'),
            <<<MARKDOWN
                <!-- markdownlint-disable -->

                {$actual}
                MARKDOWN,
        );
    }
}
