<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use Mockery;
use Symfony\Component\Console\Output\BufferedOutput;

use function array_map;
use function explode;
use function get_class;
use function implode;
use function json_decode;
use function json_encode;
use function str_replace;
use function trim;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Migrator\Extenders\SmartMigrator
 */
class SmartMigratorTest extends TestCase {
    /**
     * @covers \LastDragon_ru\LaraASP\Migrator\Provider
     */
    public function testProvider(): void {
        self::assertEquals(SmartMigrator::class, get_class($this->app->make('migrator')));
    }

    public function testMigrate(): void {
        // Prepare
        $path         = self::getTestData()->path('/migrations');
        $migrations   = [
            ['migration' => '2021_05_09_055650_raw_migration_a'],
            ['migration' => '2021_05_09_055655_raw_data_migration_a'],
            ['migration' => '2021_05_09_055655_raw_migration_b'],
            ['migration' => '2021_05_09_055650_anonymous'],
        ];
        $expectedUp   = '9.0+up.txt';
        $expectedDown = '9.0+down.txt';

        if (InstalledVersions::satisfies(new VersionParser(), 'laravel/framework', '>=9.26.0')) {
            # Since v9.26.0 commands output was slightly changed
            #
            # https://github.com/laravel/framework/pull/43769
            $expectedUp   = '9.26+up.txt';
            $expectedDown = '9.26+down.txt';
        } elseif (InstalledVersions::satisfies(new VersionParser(), 'laravel/framework', '>=9.21.0')) {
            # Since v9.21.0 commands output was changed
            #
            # https://github.com/laravel/framework/releases/tag/v9.21.0
            # https://github.com/laravel/framework/pull/43065
            $expectedUp   = '9.21+up.txt';
            $expectedDown = '9.21+down.txt';
        } else {
            // empty
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
            ->andReturn(json_decode((string) json_encode($migrations)));

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

        self::assertEquals(
            $this->prepare(self::getTestData()->content($expectedUp)),
            $this->prepare($output->fetch()),
        );

        // Down
        $migrator->rollback($path, [
            'pretend' => true,
        ]);

        self::assertEquals(
            $this->prepare(self::getTestData()->content($expectedDown)),
            $this->prepare($output->fetch()),
        );
    }

    private function prepare(string $content): string {
        return trim(implode("\n", array_map('rtrim', explode("\n", str_replace("\r\n", "\n", $content)))));
    }
}
