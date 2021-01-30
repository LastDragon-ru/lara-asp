<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

/**
 * @mixin \Illuminate\Support\ServiceProvider
 */
trait ProviderWithTranslations {
    use ProviderHelper;

    protected function bootTranslations() {
        $package = self::Package;
        $path    = $this->getPath('../resources/lang');

        $this->loadTranslationsFrom($path, $package);
        $this->publishes([
            $path => $this->app->resourcePath("lang/vendor/{$package}"),
        ], 'translations');
    }
}
