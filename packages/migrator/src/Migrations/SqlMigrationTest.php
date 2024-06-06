<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Database\Connection;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Mockery\MockProperties;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function pathinfo;

use const PATHINFO_FILENAME;

/**
 * @internal
 */
#[CoversClass(SqlMigration::class)]
final class SqlMigrationTest extends TestCase {
    public function testUp(): void {
        // Prepare
        $aConnection = Mockery::mock(Connection::class);
        $aConnection
            ->shouldReceive('getDriverName')
            ->atLeast()
            ->once()
            ->andReturn('a');
        $bConnection = Mockery::mock(Connection::class);
        $migrator    = Mockery::mock(Migrator::class);
        $migrator
            ->shouldReceive('resolveConnection')
            ->with('')
            ->atLeast()
            ->once()
            ->andReturn(
                $aConnection,
            );
        $migrator
            ->shouldReceive('resolveConnection')
            ->with('b')
            ->atLeast()
            ->once()
            ->andReturn(
                $bConnection,
            );

        // No file, No Connection
        $migration = Mockery::mock(SqlMigration::class, MockProperties::class);
        $migration->shouldAllowMockingProtectedMethods();
        $migration->makePartial();
        $migration
            ->shouldUseProperty('migrator')
            ->value(
                $migrator,
            );
        $migration
            ->shouldReceive('runSqlFile')
            ->never();

        $migration
            ->up();

        // With file, No Connection
        $file      = pathinfo(__FILE__, PATHINFO_FILENAME);
        $directory = __DIR__;
        $migration = Mockery::mock(SqlMigration::class, MockProperties::class);
        $migration->shouldAllowMockingProtectedMethods();
        $migration->makePartial();
        $migration
            ->shouldUseProperty('migrator')
            ->value(
                $migrator,
            );
        $migration
            ->shouldReceive('runSqlFile')
            ->withArgs(
                static function (Connection $connection, string $path) use ($aConnection, $directory, $file): bool {
                    return $aConnection === $connection
                        && Path::normalize($path) === Path::join($directory, "{$file}~up.sql");
                },
            )
            ->once()
            ->andReturns();

        $migration
            ->upFrom(__FILE__)
            ->up();

        // With file, With Driver
        $file      = pathinfo(__FILE__, PATHINFO_FILENAME);
        $directory = __DIR__;
        $migration = Mockery::mock(SqlMigration::class, MockProperties::class);
        $migration->shouldAllowMockingProtectedMethods();
        $migration->makePartial();
        $migration
            ->shouldUseProperty('migrator')
            ->value(
                $migrator,
            );
        $migration
            ->shouldReceive('runSqlFile')
            ->withArgs(
                static function (Connection $connection, string $path) use ($aConnection, $directory, $file): bool {
                    return $aConnection === $connection
                        && Path::normalize($path) === Path::join($directory, "{$file}~a.up.sql");
                },
            )
            ->once()
            ->andReturns();

        $migration
            ->withDriverName(true)
            ->upFrom(__FILE__)
            ->up();

        // With file, With Connection
        $file      = pathinfo(__FILE__, PATHINFO_FILENAME);
        $directory = __DIR__;
        $migration = Mockery::mock(SqlMigration::class, MockProperties::class);
        $migration->shouldAllowMockingProtectedMethods();
        $migration->makePartial();
        $migration
            ->shouldUseProperty('migrator')
            ->value(
                $migrator,
            );
        $migration
            ->shouldReceive('runSqlFile')
            ->withArgs(
                static function (Connection $connection, string $path) use ($bConnection, $directory, $file): bool {
                    return $bConnection === $connection
                        && Path::normalize($path) === Path::join($directory, "{$file}~up.sql");
                },
            )
            ->once()
            ->andReturns();

        $migration
            ->onConnection('b')
            ->upFrom(__FILE__)
            ->up();
    }

    public function testDown(): void {
        // Prepare
        $aConnection = Mockery::mock(Connection::class);
        $aConnection
            ->shouldReceive('getDriverName')
            ->atLeast()
            ->once()
            ->andReturn('a');
        $bConnection = Mockery::mock(Connection::class);
        $migrator    = Mockery::mock(Migrator::class);
        $migrator
            ->shouldReceive('resolveConnection')
            ->with('')
            ->atLeast()
            ->once()
            ->andReturn(
                $aConnection,
            );
        $migrator
            ->shouldReceive('resolveConnection')
            ->with('b')
            ->atLeast()
            ->once()
            ->andReturn(
                $bConnection,
            );

        // No file, No Connection
        $migration = Mockery::mock(SqlMigration::class, MockProperties::class);
        $migration->shouldAllowMockingProtectedMethods();
        $migration->makePartial();
        $migration
            ->shouldUseProperty('migrator')
            ->value(
                $migrator,
            );
        $migration
            ->shouldReceive('runSqlFile')
            ->never();

        $migration
            ->down();

        // With file, No Connection
        $file      = pathinfo(__FILE__, PATHINFO_FILENAME);
        $directory = __DIR__;
        $migration = Mockery::mock(SqlMigration::class, MockProperties::class);
        $migration->shouldAllowMockingProtectedMethods();
        $migration->makePartial();
        $migration
            ->shouldUseProperty('migrator')
            ->value(
                $migrator,
            );
        $migration
            ->shouldReceive('runSqlFile')
            ->withArgs(
                static function (Connection $connection, string $path) use ($aConnection, $directory, $file): bool {
                    return $aConnection === $connection
                        && Path::normalize($path) === Path::join($directory, "{$file}~down.sql");
                },
            )
            ->once()
            ->andReturns();

        $migration
            ->downFrom(__FILE__)
            ->down();

        // With file, With Driver
        $file      = pathinfo(__FILE__, PATHINFO_FILENAME);
        $directory = __DIR__;
        $migration = Mockery::mock(SqlMigration::class, MockProperties::class);
        $migration->shouldAllowMockingProtectedMethods();
        $migration->makePartial();
        $migration
            ->shouldUseProperty('migrator')
            ->value(
                $migrator,
            );
        $migration
            ->shouldReceive('runSqlFile')
            ->withArgs(
                static function (Connection $connection, string $path) use ($aConnection, $directory, $file): bool {
                    return $aConnection === $connection
                        && Path::normalize($path) === Path::join($directory, "{$file}~a.down.sql");
                },
            )
            ->once()
            ->andReturns();

        $migration
            ->withDriverName(true)
            ->downFrom(__FILE__)
            ->down();

        // With file, With Connection
        $file      = pathinfo(__FILE__, PATHINFO_FILENAME);
        $directory = __DIR__;
        $migration = Mockery::mock(SqlMigration::class, MockProperties::class);
        $migration->shouldAllowMockingProtectedMethods();
        $migration->makePartial();
        $migration
            ->shouldUseProperty('migrator')
            ->value(
                $migrator,
            );
        $migration
            ->shouldReceive('runSqlFile')
            ->withArgs(
                static function (Connection $connection, string $path) use ($bConnection, $directory, $file): bool {
                    return $bConnection === $connection
                        && Path::normalize($path) === Path::join($directory, "{$file}~down.sql");
                },
            )
            ->once()
            ->andReturns();

        $migration
            ->onConnection('b')
            ->downFrom(__FILE__)
            ->down();
    }
}
