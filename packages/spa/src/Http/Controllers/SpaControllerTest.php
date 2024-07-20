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
use LastDragon_ru\LaraASP\Testing\Providers\DataProvider as DataProviderContract;
use LastDragon_ru\LaraASP\Testing\Providers\ExpectedFinal;
use LastDragon_ru\LaraASP\Testing\Providers\UnknownValue;
use LastDragon_ru\LaraASP\Testing\Responses\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(SpaController::class)]
final class SpaControllerTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<array-key, mixed> $headers
     * @param array<array-key, mixed> $settings
     */
    #[DataProvider('dataProviderSettings')]
    public function testSettings(
        Response $expected,
        bool $routes = true,
        ?string $prefix = null,
        array $headers = [],
        array $settings = [],
    ): void {
        $this->setConfig([
            Package::Name.'.routes.enabled' => $routes,
            Package::Name.'.routes.prefix'  => $prefix,
            Package::Name.'.spa'            => $settings,
        ]);

        $provider = new class($this->app()) extends Provider {
            // empty
        };
        $provider->boot();
        $provider->callBootedCallbacks();

        $this->get("{$prefix}/settings", $headers)->assertThat($expected);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderSettings(): array {
        return (new CompositeDataProvider(
            self::getEnabledDataProvider(),
            self::getPrefixDataProvider(),
            self::getAcceptDataProvider(),
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

    protected static function getEnabledDataProvider(): DataProviderContract {
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

    protected static function getPrefixDataProvider(): DataProviderContract {
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

    protected static function getAcceptDataProvider(): DataProviderContract {
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
}
