<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;

class Provider extends ServiceProvider {
    use ProviderWithConfig;

    public function boot(): void {
        $this->bootConfig();
    }

    protected function getName(): string {
        return Package::Name;
    }
}
