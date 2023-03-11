<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Controllers;

use LastDragon_ru\LaraASP\Spa\Package;
use LastDragon_ru\LaraASP\Spa\Provider;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchemaFile;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\DataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\ExpectedFinal;
use LastDragon_ru\LaraASP\Testing\Providers\UnknownValue;
use LastDragon_ru\LaraASP\Testing\Responses\JsonResponse;

use function config;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Spa\Http\Controllers\SpaController
 */
class SpaControllerTest extends TestCase {
    // <editor-fold desc="Prepare">
    // =========================================================================
    /**
     * @inheritDoc
     */
    protected function getEnvironmentSetUp($app): void {
        parent::getEnvironmentSetUp($app);

        config([
            Package::Name.'.routes.enabled' => false,
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderSettings
     *
     * @param array<mixed> $headers
     * @param array<mixed> $settings
     */
    public function testSettings(
        Response $expected,
        bool $routes = true,
        string $prefix = null,
        array $headers = [],
        array $settings = [],
    ): void {
        config([
            Package::Name.'.routes.enabled' => $routes,
            Package::Name.'.routes.prefix'  => $prefix,
            Package::Name.'.spa'            => $settings,
        ]);

        $this->loadRoutes();

        $this->get("{$prefix}/settings", $headers)->assertThat($expected);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderSettings(): array {
        return (new CompositeDataProvider(
            $this->getEnabledDataProvider(),
            $this->getPrefixDataProvider(),
            $this->getAcceptDataProvider(),
            new ArrayDataProvider([
                'settings returned (default)' => [
                    new JsonResponse(
                        new Ok(),
                        new JsonSchemaFile(self::getTestData()->file('.settings.default.json')),
                    ),
                    [],
                ],
                'settings returned (custom)'  => [
                    new JsonResponse(
                        new Ok(),
                        new JsonSchemaFile(self::getTestData()->file('.settings.custom.json')),
                    ),
                    [
                        'custom' => 'value',
                    ],
                ],
            ]),
        ))->getData();
    }

    protected function getEnabledDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'disabled' => [
                new ExpectedFinal(new NotFound()),
                false,
            ],
            'enabled'  => [
                new UnknownValue(),
                true,
            ],
        ]);
    }

    protected function getPrefixDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'without prefix' => [
                new UnknownValue(),
                null,
            ],
            'with prefix'    => [
                new UnknownValue(),
                'spa',
            ],
        ]);
    }

    protected function getAcceptDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'accept html' => [
                new UnknownValue(),
                [
                    'Accept' => 'text/html',
                ],
            ],
            'accept json' => [
                new UnknownValue(),
                [
                    'Accept' => 'application/json',
                ],
            ],
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function loadRoutes(): void {
        (new class($this->app) extends Provider {
            public function bootRoutes(): void {
                parent::bootRoutes();
            }
        })->bootRoutes();
    }
    // </editor-fold>
}
