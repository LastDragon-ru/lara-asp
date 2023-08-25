<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\DocumentTitleIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageComposerJsonIsMissing;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PackageReadmeIsMissing;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function basename;

/**
 * @internal
 */
#[CoversClass(IncludePackageList::class)]
class IncludePackageListTest extends TestCase {
    public function testProcess(): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/packages'));
        $instance = $this->app->make(IncludePackageList::class);
        $actual   = $instance->process($path, $target);

        self::assertEquals(
            self::getTestData()->content('.md'),
            <<<MARKDOWN
            <!-- markdownlint-disable -->

            {$actual}
            MARKDOWN,
        );
    }

    public function testProcessNotAPackage(): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/invalid'));
        $instance = $this->app->make(IncludePackageList::class);

        self::expectExceptionObject(
            new PackageComposerJsonIsMissing(
                $path,
                $target,
                'invalid/package',
            ),
        );

        $instance->process($path, $target);
    }

    public function testProcessNoReadme(): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/no readme'));
        $instance = $this->app->make(IncludePackageList::class);

        self::expectExceptionObject(
            new PackageReadmeIsMissing(
                $path,
                $target,
                'no readme/package',
            ),
        );

        $instance->process($path, $target);
    }

    public function testProcessNoTitle(): void {
        $path     = self::getTestData()->file('Document.md')->getPathname();
        $target   = basename(self::getTestData()->path('/no title'));
        $instance = $this->app->make(IncludePackageList::class);

        self::expectExceptionObject(
            new DocumentTitleIsMissing(
                $path,
                $target,
                'no title/package/README.md',
            ),
        );

        $instance->process($path, $target);
    }
}
