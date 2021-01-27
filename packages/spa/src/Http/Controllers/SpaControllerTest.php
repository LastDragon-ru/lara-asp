<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Controllers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Illuminate\View\Factory;
use LastDragon_ru\LaraASP\Core\Provider as CoreProvider;
use LastDragon_ru\LaraASP\Spa\Provider;
use LastDragon_ru\LaraASP\Testing\Constraints\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Body;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\HtmlContentType;
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
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\Filesystem\Filesystem;
use function array_merge;
use function implode;
use function str_replace;
use function sys_get_temp_dir;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Spa\Http\Controllers\SpaController
 */
class SpaControllerTest extends TestCase {
    use WithTestData;

    private string     $tmp;
    private Filesystem $fs;

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

    public function setUp(): void {
        // Parent
        parent::setUp();

        // Create tmp dir
        $this->tmp = implode('/', [sys_get_temp_dir(), Provider::Package]);
        $this->fs  = new Filesystem();

        $this->fs->mkdir($this->tmp);

        // Create view
        $this->createView('index');

        // Set view path
        $this->app->make(Factory::class)->addLocation($this->tmp);
    }

    public function tearDown(): void {
        // Cleanup
        $this->fs->remove($this->tmp);

        unset($this->fs);
        unset($this->tmp);

        // Parent
        parent::tearDown();
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::index
     *
     * @dataProvider dataProviderIndex
     *
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Response\Response $expected
     * @param bool                                                         $enabled
     * @param string|null                                                  $prefix
     * @param array                                                        $headers
     * @param string|null                                                  $view
     *
     * @return void
     */
    public function testIndex(Response $expected, bool $enabled = true, string $prefix = null, array $headers = [], string $view = null): void {
        $this->setSettings([
            'routes.enabled' => $enabled,
            'routes.prefix'  => $prefix,
        ]);

        $this->loadRoutes();

        if ($view) {
            $this->createView($view);
        }

        $this->get("{$prefix}/index", $headers)->assertThat($expected);
    }

    /**
     * @covers ::settings
     *
     * @dataProvider dataProviderSettings
     *
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Response\Response $expected
     * @param bool                                                         $enabled
     * @param string|null                                                  $prefix
     * @param array                                                        $headers
     * @param array                                                        $settings
     *
     * @return void
     */
    public function testSettings(Response $expected, bool $enabled = true, string $prefix = null, array $headers = [], array $settings = []): void {
        $this->setSettings([
            'routes.enabled' => $enabled,
            'routes.prefix'  => $prefix,
            'spa'            => $settings,
        ]);

        $this->loadRoutes();

        $this->get("{$prefix}/settings", $headers)->assertThat($expected);
    }

    /**
     * @covers ::settings
     *
     * @dataProvider dataProviderUnknown
     *
     * @param \LastDragon_ru\LaraASP\Testing\Constraints\Response\Response $expected
     * @param bool                                                         $enabled
     * @param string|null                                                  $prefix
     * @param array                                                        $headers
     * @param string|null                                                  $view
     *
     * @return void
     */
    public function testUnknown(Response $expected, bool $enabled = true, string $prefix = null, array $headers = [], string $view = null): void {
        $this->setSettings([
            'routes.enabled' => $enabled,
            'routes.prefix'  => $prefix,
        ]);

        $this->loadRoutes();

        if ($view) {
            $this->createView($view);
        }

        $this->get($prefix.'/'.Str::random(), $headers)->assertThat($expected);
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderIndex(): array {
        return (new CompositeDataProvider(
            $this->getEnabledDataProvider(),
            $this->getPrefixDataProvider(),
            $this->getAcceptHtmlDataProvider(),
            new ArrayDataProvider([
                'no spa view'   => [
                    new Response(
                        new Ok(),
                        new HtmlContentType(),
                        new Body(
                            new IsEqual('index')
                        )
                    ),
                    null,
                ],
                'spa view used' => [
                    new Response(
                        new Ok(),
                        new HtmlContentType(),
                        new Body(
                            new IsEqual('spa.index')
                        )
                    ),
                    'spa.index',
                ],
            ])
        ))->getData();
    }

    public function dataProviderSettings(): array {
        return (new CompositeDataProvider(
            $this->getEnabledDataProvider(),
            $this->getPrefixDataProvider(),
            $this->getAcceptJsonDataProvider(),
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

    public function dataProviderUnknown(): array {
        return (new CompositeDataProvider(
            $this->getEnabledDataProvider(),
            $this->getPrefixDataProvider(),
            $this->getAcceptHtmlDataProvider(),
            new ArrayDataProvider([
                'no spa view'   => [
                    new Response(
                        new Ok(),
                        new HtmlContentType(),
                        new Body(
                            new IsEqual('index')
                        )
                    ),
                    null,
                ],
                'spa view used' => [
                    new Response(
                        new Ok(),
                        new HtmlContentType(),
                        new Body(
                            new IsEqual('spa.index')
                        )
                    ),
                    'spa.index',
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

    protected function getAcceptHtmlDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'accept html' => [
                new Ok(),
                [
                    'Accept' => 'text/html',
                ],
            ],
            'accept json' => [
                new ExpectedFinal(new NotFound()),
                [
                    'Accept' => 'application/json',
                ],
            ],
        ]);
    }

    protected function getAcceptJsonDataProvider(): DataProvider {
        return new ArrayDataProvider([
            'accept html' => [
                new ExpectedFinal(new Response(
                    new Ok(),
                    new HtmlContentType()
                )),
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
    protected function createView(string $path): void {
        $content = $path;
        $path    = str_replace('.', '/', $path);
        $path    = "{$this->tmp}/{$path}.blade.php";

        $this->fs->dumpFile($path, $content);
    }

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
