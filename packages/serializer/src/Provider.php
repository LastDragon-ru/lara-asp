<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;

class Provider extends ServiceProvider {
    use ProviderWithConfig;

    public function register(): void {
        parent::register();

        $this->app->scopedIf(Serializer::class, static function (Container $container): Serializer {
            return $container->make(Factory::class)->create();
        });
    }

    public function boot(): void {
        $this->bootConfig();
    }

    protected function getName(): string {
        return Package::Name;
    }
}
