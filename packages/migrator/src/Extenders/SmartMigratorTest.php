<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator\Extenders;

use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Filesystem\Filesystem;
use LastDragon_ru\LaraASP\Migrator\Provider;
use LastDragon_ru\LaraASP\Migrator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Output\BufferedOutput;

use function array_map;
use function explode;
use function get_class;
use function implode;
use function json_decode;
use function json_encode;
use function rtrim;
use function str_replace;
use function trim;

/**
 * @internal
 */
#[CoversClass(SmartMigrator::class)]
#[CoversClass(Provider::class)]
final class SmartMigratorTest extends TestCase {
    public function testProvider(): void {
        self::assertEquals(SmartMigrator::class, get_class(Container::getInstance()->make('migrator')));
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
        $expectedUp   = 'up.txt';
        $expectedDown = 'down.txt';

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
            Container::getInstance()->make(ConnectionResolverInterface::class),
            Container::getInstance()->make(Filesystem::class),
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
        return trim(implode("\n", array_map(rtrim(...), explode("\n", str_replace("\r\n", "\n", $content)))));
    }
}
