<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithConfig;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Override;

class Provider extends ServiceProvider {
    use WithConfig;

    #[Override]
    public function register(): void {
        parent::register();

        $this->registerConfig(PackageConfig::class);
        $this->app->scopedIf(Serializer::class, static function (Container $container): Serializer {
            return $container->make(Factory::class)->create();
        });
    }

    #[Override]
    protected function getName(): string {
        return Package::Name;
    }
}
