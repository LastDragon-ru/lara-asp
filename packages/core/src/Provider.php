<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core;

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Application\ApplicationResolver;
use LastDragon_ru\LaraASP\Core\Application\ConfigResolver;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use Override;

class Provider extends ServiceProvider {
    #[Override]
    public function register(): void {
        parent::register();

        $this->registerResolvers();
    }

    protected function registerResolvers(): void {
        $this->app->singletonIf(ContainerResolver::class, static function (): ContainerResolver {
            return new ContainerResolver(static fn () => Container::getInstance());
        });
        $this->app->singletonIf(ApplicationResolver::class, static function (): ApplicationResolver {
            return new ApplicationResolver(static fn () => Container::getInstance()->make(Application::class));
        });
        $this->app->singletonIf(ConfigResolver::class, static function (): ConfigResolver {
            return new ConfigResolver(static fn () => Container::getInstance()->make(Repository::class));
        });
    }
}
