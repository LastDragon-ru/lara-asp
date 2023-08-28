<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Helpers\Viewer;

/**
 * @see Viewer
 *
 * @mixin ServiceProvider
 */
trait WithViews {
    use Helper;

    protected function bootViews(): void {
        $package = $this->getName();
        $path    = $this->getPath('../defaults/views');

        $this->loadViewsFrom($path, $package);
        $this->publishes([
            $path => $this->app->resourcePath("views/vendor/{$package}"),
        ], 'views');
    }
}
