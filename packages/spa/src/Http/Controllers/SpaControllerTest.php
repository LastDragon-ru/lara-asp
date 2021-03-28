<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Controllers;

use Closure;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Spa\Http\Resources\Scalar\NullResource;
use LastDragon_ru\LaraASP\Spa\Http\Resources\UserResource;
use LastDragon_ru\LaraASP\Spa\Package;
use LastDragon_ru\LaraASP\Spa\Provider;
use LastDragon_ru\LaraASP\Spa\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\NotFound;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Ok;
use LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\DataProvider;
use LastDragon_ru\LaraASP\Testing\Providers\ExpectedFinal;
use LastDragon_ru\LaraASP\Testing\Providers\Unknown;
use LastDragon_ru\LaraASP\Testing\Responses\JsonResponse;
use LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json\OkResponse;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Http\Controllers\SpaController
 */
class SpaControllerTest extends TestCase {
    use WithTestData;

    // <editor-fold desc="Prepare">
    // =========================================================================
    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app): void {
        parent::getEnvironmentSetUp($app);

        $this->setSettings([
            'routes.enabled' => false,
        ], $app);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::settings
     *
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
        $this->setSettings([
            'routes.enabled' => $routes,
            'routes.prefix'  => $prefix,
            'spa'            => $settings,
        ]);

        $this->loadRoutes();

        $this->get("{$prefix}/settings", $headers)->assertThat($expected);
    }

    /**
     * @covers ::user
     *
     * @dataProvider dataProviderUser
     *
     * @param array<mixed> $headers
     */
    public function testUser(
        Response $expected,
        bool $routes = true,
        string $prefix = null,
        array $headers = [],
        Closure $user = null,
    ): void {
        $this->setSettings([
            'routes.enabled' => $routes,
            'routes.prefix'  => $prefix,
        ]);

        $this->loadRoutes();

        if ($user) {
            $this->actingAs($user());
        }

        $this->get("{$prefix}/user", $headers)->assertThat($expected);
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
                    new JsonResponse(new Ok(), new JsonSchema($this->getTestData()->file('.settings.default.json'))),
                    [],
                ],
                'settings returned (custom)'  => [
                    new JsonResponse(new Ok(), new JsonSchema($this->getTestData()->file('.settings.custom.json'))),
                    [
                        'custom' => 'value',
                    ],
                ],
            ]),
        ))->getData();
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderUser(): array {
        return (new CompositeDataProvider(
            $this->getEnabledDataProvider(),
            $this->getPrefixDataProvider(),
            $this->getAcceptDataProvider(),
            new ArrayDataProvider([
                'guest'                     => [
                    new OkResponse(NullResource::class),
                    null,
                ],
                'user (email not verified)' => [
                    new OkResponse(UserResource::class, [
                        'name'     => 'Test',
                        'verified' => false,
                    ]),
                    static function (): Model {
                        return (new User())->forceFill([
                            'name'              => 'Test',
                            'email_verified_at' => null,
                        ]);
                    },
                ],
                'user (email verified)'     => [
                    new OkResponse(UserResource::class, [
                        'name'     => 'Test',
                        'verified' => true,
                    ]),
                    static function (): Model {
                        return (new User())->forceFill([
                            'name'              => 'Test',
                            'email_verified_at' => Date::now(),
                        ]);
                    },
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
                new Unknown(),
                true,
            ],
        ]);
    }

    protected function getPrefixDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'without prefix' => [
                new Unknown(),
                null,
            ],
            'with prefix'    => [
                new Unknown(),
                'spa',
            ],
        ]);
    }

    protected function getAcceptDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'accept html' => [
                new Unknown(),
                [
                    'Accept' => 'text/html',
                ],
            ],
            'accept json' => [
                new Unknown(),
                [
                    'Accept' => 'application/json',
                ],
            ],
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @param array<string,mixed> $settings
     */
    protected function setSettings(array $settings, Application $app = null): void {
        $package = Package::Name;
        $config  = ($app ?? $this->app)->get(Repository::class);

        foreach ($settings as $name => $value) {
            $config->set("{$package}.{$name}", $value);
        }
    }

    protected function loadRoutes(): void {
        (new class($this->app) extends Provider {
            public function bootRoutes(): void {
                parent::bootRoutes();
            }
        })->bootRoutes();
    }
    // </editor-fold>
}
