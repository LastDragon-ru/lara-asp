<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Illuminate\Support\ServiceProvider;

/**
 * @see \LastDragon_ru\LaraASP\Core\Translator
 *
 * @mixin ServiceProvider
 */
trait ProviderWithTranslations {
    use ProviderHelper;

    protected function bootTranslations(): void {
        $package = $this->getName();
        $path    = $this->getPath('../resources/lang');

        $this->loadTranslationsFrom($path, $package);
        $this->publishes([
            $path => $this->app->resourcePath("lang/vendor/{$package}"),
        ], 'translations');
    }
}
