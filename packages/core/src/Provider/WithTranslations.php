<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Translator;

/**
 * @see Translator
 *
 * @mixin ServiceProvider
 */
trait WithTranslations {
    use Helper;

    protected function bootTranslations(): void {
        $package = $this->getName();
        $path    = $this->getPath('../defaults/translations');

        $this->loadTranslationsFrom($path, $package);
        $this->publishes([
            $path => $this->app->langPath("vendor/{$package}"),
        ], 'translations');
    }
}
