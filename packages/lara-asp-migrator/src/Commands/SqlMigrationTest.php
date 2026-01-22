<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Commands;

use LastDragon_ru\LaraASP\Migrator\Package;
use LastDragon_ru\LaraASP\Migrator\Package\TestCase;
use LastDragon_ru\PhpUnit\Utils\TempDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;

use function array_slice;
use function explode;
use function implode;

/**
 * @internal
 */
#[CoversClass(SqlMigration::class)]
final class SqlMigrationTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderHandle')]
    public function testHandle(string $command, string $path): void {
        // Pre test
        $temp   = new TempDirectory();
        $finder = Finder::create()->in($temp->path->path);

        self::assertCount(0, $finder->files());

        // Call
        $this->artisan($command, [
            'name'   => 'SqlMigration',
            '--path' => "{$temp->path}{$path}",
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
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{string, string}>
     */
    public static function dataProviderHandle(): array {
        return [
            'name'             => [Package::Name.':sql-migration', 'path'],
            'path ends with /' => [Package::Name.':sql-migration', 'path/'],
            'alias'            => ['make:sql-migration', ''],
        ];
    }
    // </editor-fold>
}
