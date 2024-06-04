<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use LastDragon_ru\LaraASP\Migrator\Package;
use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Finder\Finder;

use function array_slice;
use function explode;
use function implode;

/**
 * @internal
 */
#[CoversClass(SqlMigration::class)]
final class SqlMigrationTest extends TestCase {
    public function testHandle(): void {
        // Pre test
        $pkg    = Package::Name;
        $path   = self::getTempDirectory();
        $finder = Finder::create()->in($path);

        self::assertCount(0, $finder->files());

        // Call
        $this->artisan("{$pkg}:sql-migration", [
            'name'   => 'SqlMigration',
            '--path' => $path,
        ]);

        // Test
        $expected = [
            'sql_migration.php',
            'sql_migration~down.sql',
            'sql_migration~up.sql',
        ];
        $actual   = [];

        foreach ($finder->files()->sortByName() as $file) {
            $actual[] = implode('_', array_slice(explode('_', $file->getFilename()), 4));
        }

        self::assertEquals($expected, $actual);
    }
}
