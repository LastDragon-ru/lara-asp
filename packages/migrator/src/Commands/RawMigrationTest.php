<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Composer;
use LastDragon_ru\LaraASP\Migrator\Package;
use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use Mockery;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

use function array_slice;
use function explode;
use function implode;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Migrator\Commands\RawMigration
 */
class RawMigrationTest extends TestCase {
    public function testHandle(): void {
        // make:migration may also call dump-autoload we are no need this.
        $composer = Mockery::mock(Composer::class);

        if (InstalledVersions::satisfies(new VersionParser(), 'laravel/framework', '>=10.1.5')) {
            $composer
                ->shouldReceive('dumpAutoloads')
                ->never();
        } else {
            $composer
                ->shouldReceive('dumpAutoloads')
                ->once()
                ->andReturns();
        }

        $this->override(Composer::class, static function () use ($composer) {
            return $composer;
        });

        // Pre test
        $pkg    = Package::Name;
        $path   = self::getTempDirectory();
        $finder = Finder::create()->in($path);

        self::assertCount(0, $finder->files());

        // Call
        $this->artisan("{$pkg}:raw-migration", [
            'name'       => 'RawMigration',
            '--path'     => $path,
            '--realpath' => true,
        ]);

        // Test
        $expected = [
            'raw_migration.php',
            'raw_migration~down.sql',
            'raw_migration~up.sql',
        ];
        $actual   = (new Collection($finder->files()->sortByName()))
            ->map(static function (SplFileInfo $file): string {
                return implode('_', array_slice(explode('_', $file->getFilename()), 4));
            })
            ->values()
            ->all();

        self::assertEquals($expected, $actual);
    }
}
