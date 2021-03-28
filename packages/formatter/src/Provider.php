<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithTranslations;

class Provider extends ServiceProvider {
    use ProviderWithConfig;
    use ProviderWithTranslations;

    public function boot(): void {
        $this->bootConfig();
        $this->bootTranslations();
    }

    protected function getName(): string {
        return Package::Name;
    }
}
