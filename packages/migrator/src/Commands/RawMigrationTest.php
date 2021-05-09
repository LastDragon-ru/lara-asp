<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use LastDragon_ru\LaraASP\Migrator\Package;
use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use Symfony\Component\Finder\Finder;

use function array_keys;
use function iterator_to_array;
use function mkdir;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Migrator\Commands\RawMigration
 */
class RawMigrationTest extends TestCase {
    /**
     * @covers ::handle
     */
    public function testHandle(): void {
        $pkg  = Package::Name;
        $path = tempnam(sys_get_temp_dir(), $pkg);

        unlink($path);
        mkdir($path);

        $finder = Finder::create()->in($path);

        $this->assertCount(0, $finder->files());

        $this->artisan("{$pkg}:raw-migration", [
            'name'       => 'RawMigration',
            '--path'     => $path,
            '--realpath' => true,
        ]);

        $this->assertCount(3, $finder->files());
    }
}
