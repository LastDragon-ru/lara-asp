<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithConfig;
use LastDragon_ru\LaraASP\Core\Provider\WithRoutes;
use LastDragon_ru\LaraASP\Core\Provider\WithTranslations;
use Override;

class PackageProvider extends ServiceProvider {
    use WithConfig;
    use WithRoutes;
    use WithTranslations;

    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    #[Override]
    public function register(): void {
        parent::register();

        $this->registerConfig(PackageConfig::class);
    }

    public function boot(): void {
        $this->bootTranslations();
        $this->bootRoutes(function (): array {
            $config   = $this->app->make(PackageConfig::class)->getInstance();
            $settings = [
                'enabled'    => $config->routes->enabled,
                'attributes' => [
                    'prefix'     => $config->routes->prefix,
                    'middleware' => $config->routes->middleware,
                ],
            ];

            return $settings;
        });
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    #[Override]
    protected function getName(): string {
        return Package::Name;
    }
    // </editor-fold>
}
