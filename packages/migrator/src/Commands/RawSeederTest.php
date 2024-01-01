<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\Migrator\Package;
use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
#[CoversClass(RawSeeder::class)]
class RawSeederTest extends TestCase {
    public function testHandle(): void {
        // Pre test
        $pkg    = Package::Name;
        $path   = self::getTempDirectory();
        $finder = Finder::create()->in($path);

        self::assertCount(0, $finder->files());

        // Redefine path where files will be generated.
        self::assertNotNull($this->app);

        $this->app->useDatabasePath($path);

        // Call
        $this->artisan("{$pkg}:raw-seeder", [
            'name' => 'RawSeeder',
        ]);

        // Test
        $expected = [
            'RawSeeder.php',
            'RawSeeder.sql',
        ];
        $actual   = (new Collection($finder->files()->sortByName()))
            ->map(static function (SplFileInfo $file): string {
                return $file->getFilename();
            })
            ->values()
            ->all();

        self::assertEquals($expected, $actual);
    }
}
