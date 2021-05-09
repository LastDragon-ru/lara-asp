<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use Mockery;
use Symfony\Component\Console\Output\BufferedOutput;

use function json_decode;
use function json_encode;
use function trim;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Migrator\Extenders\SmartMigrator
 */
class SmartMigratorTest extends TestCase {
    /**
     * @covers \LastDragon_ru\LaraASP\Migrator\Provider::registerMigrator
     */
    public function testProvider(): void {
        $this->assertInstanceOf(SmartMigrator::class, $this->app->make('migrator'));
    }

    /**
     * @covers ::run
     * @covers ::rollback
     * @covers \LastDragon_ru\LaraASP\Migrator\Migrations\RawMigration::__construct
     * @covers \LastDragon_ru\LaraASP\Migrator\Migrations\RawDataMigration::__construct
     */
    public function testMigrate(): void {
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
            ->andReturn(json_decode(json_encode([
                ['migration' => '2021_05_09_055650_anonymous'],
                ['migration' => '2021_05_09_055650_raw_migration_a'],
                ['migration' => '2021_05_09_055655_raw_data_migration_a'],
                ['migration' => '2021_05_09_055655_raw_migration_b'],
            ])));

        // Vars
        $migrator = new SmartMigrator(
            $repository,
            $this->app->make(ConnectionResolverInterface::class),
            $this->app->make(Filesystem::class),
        );
        $output   = new BufferedOutput();
        $path     = $this->getTestData()->path('/');

        $migrator->setOutput($output);

        // Up
        $migrator->run($path, [
            'pretend' => true,
        ]);

        $this->assertEquals(
            trim($this->getTestData()->content('~migrate-up.txt')),
            trim($output->fetch()),
        );

        // Down
        $migrator->rollback($path, [
            'pretend' => true,
        ]);

        $this->assertEquals(
            trim($this->getTestData()->content('~migrate-down.txt')),
            trim($output->fetch()),
        );
    }
}
