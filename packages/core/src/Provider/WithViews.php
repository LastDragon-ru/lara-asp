<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Illuminate\Support\ServiceProvider;

/**
 * @mixin ServiceProvider
 */
trait WithViews {
    use Helper;

    protected function bootViews(): void {
        $package = $this->getName();
        $path    = $this->getPath('../resources/views');

        $this->loadViewsFrom($path, $package);
        $this->publishes([
            $path => $this->app->resourcePath("views/vendor/{$package}"),
        ], 'views');
    }
}
