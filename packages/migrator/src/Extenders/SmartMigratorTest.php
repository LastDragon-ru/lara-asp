<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use Mockery;
use Symfony\Component\Console\Output\BufferedOutput;

use function array_merge;
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
        // Prepare
        $path         = $this->getTestData()->path('/named');
        $migrations   = [
            ['migration' => '2021_05_09_055650_raw_migration_a'],
            ['migration' => '2021_05_09_055655_raw_data_migration_a'],
            ['migration' => '2021_05_09_055655_raw_migration_b'],
        ];
        $expectedUp   = '~migrate-up.txt';
        $expectedDown = '~migrate-down.txt';

        if (SmartMigrator::isAnonymousMigrationsSupported()) {
            $path         = $this->getTestData()->path('/');
            $migrations   = array_merge([
                ['migration' => '2021_05_09_055650_anonymous'],
            ], $migrations);
            $expectedUp   = '~migrate-up-anonymous.txt';
            $expectedDown = '~migrate-down-anonymous.txt';
        }

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
            ->andReturn(json_decode(json_encode($migrations)));

        // Vars
        $migrator = new SmartMigrator(
            $repository,
            $this->app->make(ConnectionResolverInterface::class),
            $this->app->make(Filesystem::class),
        );
        $output   = new BufferedOutput();

        $migrator->setOutput($output);

        // Up
        $migrator->run($path, [
            'pretend' => true,
        ]);

        $this->assertEquals(
            trim($this->getTestData()->content($expectedUp)),
            trim($output->fetch()),
        );

        // Down
        $migrator->rollback($path, [
            'pretend' => true,
        ]);

        $this->assertEquals(
            trim($this->getTestData()->content($expectedDown)),
            trim($output->fetch()),
        );
    }
}
