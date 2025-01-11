<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Migrations;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\PackageProvider;
use LastDragon_ru\LaraASP\Migrator\Seeders\SeederService;
use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Output\BufferedOutput;

use function array_map;
use function explode;
use function implode;
use function json_decode;
use function json_encode;
use function rtrim;
use function str_replace;
use function trim;

/**
 * @internal
 */
#[CoversClass(Migrator::class)]
#[CoversClass(PackageProvider::class)]
final class MigratorTest extends TestCase {
    public function testProvider(): void {
        $defaultMigrator = $this->app()->make('migrator');
        $smartMigrator   = $this->app()->make(Migrator::class);

        self::assertInstanceOf(Migrator::class, $defaultMigrator);
        self::assertSame($defaultMigrator, $smartMigrator);
        self::assertSame($smartMigrator, $this->app()->make(Migrator::class));
        self::assertSame($defaultMigrator, $this->app()->make('migrator'));
    }

    public function testMigrate(): void {
        // Prepare
        $path         = self::getTestData()->path('/migrations');
        $migrations   = [
            ['migration' => '2024_05_29_055655_sql_migration_a'],
            ['migration' => '2024_05_29_055655_sql_migration_b'],
            ['migration' => '2024_05_29_055655_sql_migration_up_only'],
            ['migration' => '2024_05_29_055755_sql_migration_down_only'],
        ];
        $expectedUp   = 'migrations.up.txt';
        $expectedDown = 'migrations.down.txt';

        // Mocks
        $repository = Mockery::mock(MigrationRepositoryInterface::class);
        $repository
            ->shouldReceive('getRan')
            ->once()
            ->andReturn([]);
        $repository
            ->shouldReceive('getNextBatchNumber')
            ->once()
            ->andReturn(1);
        $repository
            ->shouldReceive('getLast')
            ->once()
            ->andReturn(json_decode((string) json_encode($migrations)));

        // Vars
        $migrator = new Migrator(
            $repository,
            $this->app()->make(ConnectionResolverInterface::class),
            $this->app()->make(Filesystem::class),
        );
        $output   = new BufferedOutput();

        $migrator->setOutput($output);

        // Up
        $migrator->run($path, [
            'pretend' => true,
        ]);

        self::assertSame(
            $this->prepare(self::getTestData()->content($expectedUp)),
            $this->prepare($output->fetch()),
        );

        // Down
        $migrator->rollback($path, [
            'pretend' => true,
        ]);

        self::assertSame(
            $this->prepare(self::getTestData()->content($expectedDown)),
            $this->prepare($output->fetch()),
        );
    }

    public function testMigrateRaw(): void {
        // Prepare
        $path         = self::getTestData()->path('/raw');
        $migrations   = [
            ['migration' => '2021_05_09_055650_raw_migration_a'],
            ['migration' => '2021_05_09_055655_raw_data_migration_a'],
            ['migration' => '2021_05_09_055655_raw_migration_b'],
            ['migration' => '2021_05_09_055650_anonymous'],
        ];
        $expectedUp   = 'raw.up.txt';
        $expectedDown = 'raw.down.txt';

        // Mocks
        $repository = Mockery::mock(MigrationRepositoryInterface::class);
        $repository
            ->shouldReceive('getRan')
            ->once()
            ->andReturn([]);
        $repository
            ->shouldReceive('getNextBatchNumber')
            ->once()
            ->andReturn(1);
        $repository
            ->shouldReceive('getLast')
            ->once()
            ->andReturn(json_decode((string) json_encode($migrations)));

        $this->override(SeederService::class, static function (MockInterface $mock): void {
            $mock
                ->shouldReceive('isSeeded')
                ->twice()
                ->andReturn(true);
        });

        // Vars
        $migrator = new Migrator(
            $repository,
            $this->app()->make(ConnectionResolverInterface::class),
            $this->app()->make(Filesystem::class),
        );
        $output   = new BufferedOutput();

        $migrator->setOutput($output);

        // Up
        $migrator->run($path, [
            'pretend' => true,
        ]);

        self::assertSame(
            $this->prepare(self::getTestData()->content($expectedUp)),
            $this->prepare($output->fetch()),
        );

        // Down
        $migrator->rollback($path, [
            'pretend' => true,
        ]);

        self::assertSame(
            $this->prepare(self::getTestData()->content($expectedDown)),
            $this->prepare($output->fetch()),
        );
    }

    private function prepare(string $content): string {
        return trim(implode("\n", array_map(rtrim(...), explode("\n", str_replace("\r\n", "\n", $content)))));
    }
}
