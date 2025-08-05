<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use Exception;
use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\Formatter\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PackageProvider::class)]
final class PackageProviderTest extends TestCase {
    public function testRegister(): void {
        self::assertSame(
            $this->app()->make(Formatter::class),
            $this->app()->make(Formatter::class),
        );
    }

    public function testConfig(): void {
        self::assertConfigurationExportable(PackageConfig::class);
    }

    /**
     * @deprecated 7.0.0 Array-base config is deprecated.
     */
    public function testLegacyConfig(): void {
        // Prepare
        $app     = $this->app();
        $config  = $app->make(Repository::class);
        $legacy  = (array) require self::getTestData()->path('~LegacyConfig.php');
        $package = Package::Name;

        $config->set($package, $legacy);

        self::assertIsArray($config->get($package));

        self::expectException(Exception::class);
        self::expectExceptionMessage(
            'Array-based config is not supported anymore. Please migrate to object-based config.',
        );

        (new PackageProvider($app))->register();
    }
}
