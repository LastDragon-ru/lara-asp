<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithConfig;
use LastDragon_ru\LaraASP\Core\Provider\WithRoutes;
use LastDragon_ru\LaraASP\Core\Provider\WithTranslations;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use Override;

class Provider extends ServiceProvider {
    use WithConfig;
    use WithRoutes;
    use WithTranslations;

    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    public function boot(): void {
        $this->bootConfig();
        $this->bootTranslations();
        $this->bootRoutes(function (): array {
            $package  = Package::Name;
            $config   = (array) $this->app->make(Repository::class)->get("{$package}.routes");
            $settings = [
                'enabled'    => Cast::toBool($config['enabled'] ?? false),
                'attributes' => [
                    'prefix'     => Cast::toString($config['prefix'] ?? ''),
                    'middleware' => Cast::toString($config['middleware'] ?? ''),
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
