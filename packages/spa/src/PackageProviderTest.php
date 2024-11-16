<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa;

use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\Spa\Config\Config;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PackageProvider::class)]
final class PackageProviderTest extends TestCase {
    public function testConfig(): void {
        self::assertConfigurationExportable(PackageConfig::class);
    }

    /**
     * @deprecated %{VERSION} Array-base config is deprecated.
     */
    public function testLegacyConfig(): void {
        // Prepare
        $app     = $this->app();
        $config  = $app->make(Repository::class);
        $legacy  = (array) require self::getTestData()->path('~LegacyConfig.php');
        $package = Package::Name;

        $config->set($package, $legacy);

        self::assertIsArray($config->get($package));

        (new PackageProvider($app))->register();

        // Test
        $expected                  = new Config();
        $expected->routes->enabled = true;
        $expected->routes->prefix  = 'spa_';
        $expected->spa             = [
            'property' => 'value',
        ];

        self::assertEquals($expected, $config->get($package));
    }
}
