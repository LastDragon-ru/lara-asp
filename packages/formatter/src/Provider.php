<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithTranslations;

class Provider extends ServiceProvider {
    use ProviderWithConfig;
    use ProviderWithTranslations;

    public const Package = 'lara-asp-formatter';

    public function boot(): void {
        $this->bootConfig([
            'options',
            'locales',
            'all',
        ]);
        $this->bootTranslations();
    }
}
