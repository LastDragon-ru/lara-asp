<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Seeders;

use Illuminate\Config\Repository;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder;
use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Migrator\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SeederService::class)]
final class SeederServiceTest extends TestCase {
    public function testIsSeeded(): void {
        // Prepare
        $migrations = 'migrations_table_name';
        $builder    = Mockery::mock(Builder::class);
        $builder
            ->shouldReceive('getTables')
            ->once()
            ->andReturn([
                ['name' => $migrations],
                ['name' => 'a'],
                ['name' => 'b'],
            ]);

        $aQuery = Mockery::mock(QueryBuilder::class);
        $aQuery
            ->shouldReceive('count')
            ->once()
            ->andReturn(0);

        $bQuery = Mockery::mock(QueryBuilder::class);
        $bQuery
            ->shouldReceive('count')
            ->once()
            ->andReturn(1);

        $connection = Mockery::mock(Connection::class);
        $connection
            ->shouldReceive('getSchemaBuilder')
            ->once()
            ->andReturn($builder);
        $connection
            ->shouldReceive('table')
            ->with('a')
            ->andReturn($aQuery);
        $connection
            ->shouldReceive('table')
            ->with('b')
            ->andReturn($bQuery);

        $manager = Mockery::mock(DatabaseManager::class);
        $manager
            ->shouldReceive('connection')
            ->with(null)
            ->andReturn($connection);

        $config  = Mockery::mock(ConfigResolver::class);
        $service = Mockery::mock(SeederService::class, [$config, $manager]);
        $service->shouldAllowMockingProtectedMethods();
        $service->makePartial();
        $service
            ->shouldReceive('getMigrationsTable')
            ->once()
            ->andReturn($migrations);

        // Test
        self::assertTrue($service->isSeeded());
        self::assertTrue($service->isSeeded());
    }

    public function testGetConnection(): void {
        $connection = Mockery::mock(Connection::class);
        $config     = Mockery::mock(ConfigResolver::class);
        $name       = 'another';
        $another    = Mockery::mock(Connection::class);
        $manager    = Mockery::mock(DatabaseManager::class);
        $manager
            ->shouldReceive('connection')
            ->with(null)
            ->andReturn($connection);
        $manager
            ->shouldReceive('connection')
            ->with($name)
            ->andReturn($another);

        $service = new SeederService($config, $manager);

        self::assertSame($connection, $service->getConnection());
        self::assertSame($another, $service->getConnection($name));
    }

    public function testGetMigrationsTable(): void {
        $expected = 'migrations_table_name';
        $manager  = Mockery::mock(DatabaseManager::class);
        $config   = new Repository([
            'database' => [
                'migrations' => [
                    'table' => $expected,
                ],
            ],
        ]);
        $resolver = Mockery::mock(ConfigResolver::class);
        $resolver
            ->shouldReceive('getInstance')
            ->once()
            ->andReturn($config);

        $service = new class($resolver, $manager) extends SeederService {
            #[Override]
            public function getMigrationsTable(): string {
                return parent::getMigrationsTable();
            }
        };

        self::assertSame($expected, $service->getMigrationsTable());
    }

    public function testGetMigrationsTableString(): void {
        $expected = 'migrations_table_name';
        $manager  = Mockery::mock(DatabaseManager::class);
        $config   = new Repository([
            'database' => [
                'migrations' => $expected,
            ],
        ]);
        $resolver = Mockery::mock(ConfigResolver::class);
        $resolver
            ->shouldReceive('getInstance')
            ->once()
            ->andReturn($config);

        $service = new class($resolver, $manager) extends SeederService {
            #[Override]
            public function getMigrationsTable(): string {
                return parent::getMigrationsTable();
            }
        };

        self::assertSame($expected, $service->getMigrationsTable());
    }
}
