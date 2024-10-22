<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\Core\Application\Configuration\ConfigurationResolver;
use LastDragon_ru\LaraASP\Core\Package;
use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(WithConfig::class)]
final class WithConfigTest extends TestCase {
    public function testLoadPackageConfig(): void {
        $repository  = Mockery::mock(Repository::class);
        $application = Mockery::mock(Application::class);
        $application
            ->shouldReceive('make')
            ->with(Repository::class)
            ->once()
            ->andReturn($repository);

        $provider = new WithConfigTest_Provider($application);
        $config   = $provider->getName();
        $actual   = null;

        $repository
            ->shouldReceive('get')
            ->with($config, null)
            ->once()
            ->andReturn(null);
        $repository
            ->shouldReceive('set')
            ->once()
            ->andReturnUsing(static function (mixed ...$args) use (&$actual): void {
                $actual = $args;
            });

        $provider->loadPackageConfig(WithConfigTest_ConfigurationResolver::class);

        self::assertEquals(
            [
                [
                    $config => WithConfigTest_ConfigurationResolver::getDefaultConfig(),
                ],
            ],
            $actual,
        );
    }

    public function testLoadPackageConfigDefined(): void {
        $repository  = Mockery::mock(Repository::class);
        $application = Mockery::mock(Application::class);
        $application
            ->shouldReceive('make')
            ->with(Repository::class)
            ->once()
            ->andReturn($repository);

        $provider = new WithConfigTest_Provider($application);
        $config   = $provider->getName();

        $repository
            ->shouldReceive('get')
            ->with($config, null)
            ->once()
            ->andReturn(
                WithConfigTest_ConfigurationResolver::getDefaultConfig(),
            );
        $repository
            ->shouldReceive('set')
            ->never();

        $provider->loadPackageConfig(WithConfigTest_ConfigurationResolver::class);
    }

    public function testLoadPackageConfigLegacy(): void {
        $repository  = Mockery::mock(Repository::class);
        $application = Mockery::mock(Application::class);
        $application
            ->shouldReceive('make')
            ->with(Repository::class)
            ->once()
            ->andReturn($repository);

        $provider        = new WithConfigTest_Provider($application);
        $config          = $provider->getName();
        $actual          = null;
        $expected        = new WithConfigTest_ConfigurationA();
        $expected->a     = 321;
        $expected->b     = new WithConfigTest_ConfigurationB();
        $expected->b->b  = true;
        $expected->b->bA = 'cba';

        $repository
            ->shouldReceive('get')
            ->with($config, null)
            ->once()
            ->andReturn([
                'a' => 321,
                'b' => [
                    'b'   => true,
                    'b_a' => 'cba',
                ],
            ]);
        $repository
            ->shouldReceive('set')
            ->once()
            ->andReturnUsing(static function (mixed ...$args) use (&$actual): void {
                $actual = $args;
            });

        $provider->loadPackageConfig(WithConfigTest_ConfigurationResolver::class);

        self::assertEquals(
            [
                [
                    $config => $expected,
                ],
            ],
            $actual,
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 */
class WithConfigTest_Provider extends ServiceProvider {
    use WithConfig {
        loadPackageConfig as public;
    }

    #[Override]
    public function getName(): string {
        return __METHOD__;
    }
}

/**
 * @internal
 * @extends ConfigurationResolver<WithConfigTest_ConfigurationA>
 */
class WithConfigTest_ConfigurationResolver extends ConfigurationResolver {
    #[Override]
    protected static function getName(): string {
        return Package::Name;
    }

    #[Override]
    public static function getDefaultConfig(): Configuration {
        return new WithConfigTest_ConfigurationA();
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class WithConfigTest_ConfigurationA extends Configuration {
    public function __construct(
        public int $a = 123,
        public ?WithConfigTest_ConfigurationB $b = null,
    ) {
        parent::__construct();
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class WithConfigTest_ConfigurationB extends Configuration {
    public function __construct(
        public bool $b = false,
        public string $bA = 'abc',
    ) {
        parent::__construct();
    }
}
