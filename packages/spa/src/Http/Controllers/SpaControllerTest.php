<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Controllers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Core\Provider as CoreProvider;
use LastDragon_ru\LaraASP\Spa\Provider;
use LastDragon_ru\LaraASP\Testing\Constraints\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Body;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\DataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\ExpectedFinal;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use function array_merge;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Http\Controllers\SpaController
 */
class SpaControllerTest extends TestCase {
    use WithTestData;

    // <editor-fold desc="Prepare">
    // =========================================================================
    protected function getEnvironmentSetUp($app) {
        parent::getEnvironmentSetUp($app);

        $this->setSettings([
            'routes.enabled' => false,
        ], $app);
    }

    protected function getPackageProviders($app) {
        return array_merge(parent::getPackageProviders($app), [
            CoreProvider::class,
            Provider::class,
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::settings
     *
     * @dataProvider dataProviderSettings
     *
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Response\Response $expected
     * @param bool                                                         $routes
     * @param string|null                                                  $prefix
     * @param array                                                        $headers
     * @param array                                                        $settings
     *
     * @return void
     */
    public function testSettings(Response $expected, bool $routes = true, string $prefix = null, array $headers = [], array $settings = []): void {
        $this->setSettings([
            'routes.enabled' => $routes,
            'routes.prefix'  => $prefix,
            'spa'            => $settings,
        ]);

        $this->loadRoutes();

        $this->get("{$prefix}/settings", $headers)->assertThat($expected);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderSettings(): array {
        return (new CompositeDataProvider(
            $this->getEnabledDataProvider(),
            $this->getPrefixDataProvider(),
            $this->getAcceptDataProvider(),
            new ArrayDataProvider([
                'settings returned (default)' => [
                    new Response(
                        new Ok(),
                        new JsonContentType(),
                        new Body(
                            new JsonMatchesSchema($this->getTestData()->file('.settings.default.json'))
                        )
                    ),
                    [],
                ],
                'settings returned (custom)'  => [
                    new Response(
                        new Ok(),
                        new JsonContentType(),
                        new Body(
                            new JsonMatchesSchema($this->getTestData()->file('.settings.custom.json'))
                        )
                    ),
                    [
                        'custom' => 'value',
                    ],
                ],
            ])
        ))->getData();
    }

    protected function getEnabledDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'disabled' => [
                new ExpectedFinal(new NotFound()),
                false,
            ],
            'enabled'  => [
                new Ok(),
                true,
            ],
        ]);
    }

    protected function getPrefixDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'without prefix' => [
                new Ok(),
                null,
            ],
            'with prefix'    => [
                new Ok(),
                'spa',
            ],
        ]);
    }

    protected function getAcceptDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'accept html' => [
                new Ok(),
                [
                    'Accept' => 'text/html',
                ],
            ],
            'accept json' => [
                new Ok(),
                [
                    'Accept' => 'application/json',
                ],
            ],
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function setSettings(array $settings, Application $app = null): void {
        $package = Provider::Package;
        $config  = ($app ?? $this->app)->get(Repository::class);

        foreach ($settings as $name => $value) {
            $config->set("{$package}.{$name}", $value);
        }
    }

    protected function loadRoutes(): void {
        (new class($this->app) extends Provider {
            public function bootRoutes() {
                parent::bootRoutes();
            }
        })->bootRoutes();
    }
    // </editor-fold>
}
