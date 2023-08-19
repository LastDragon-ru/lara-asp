<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithViews;
use LastDragon_ru\LaraASP\Documentator\Commands\Requirements;

class Provider extends ServiceProvider {
    use ProviderWithViews;

    public function boot(): void {
        $this->bootViews();
        $this->commands(
            Requirements::class,
        );
    }

    protected function getName(): string {
        return Package::Name;
    }
}
