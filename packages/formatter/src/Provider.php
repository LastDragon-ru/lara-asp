<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithConfig;
use LastDragon_ru\LaraASP\Core\Provider\WithTranslations;

class Provider extends ServiceProvider {
    use WithConfig;
    use WithTranslations;

    public function boot(): void {
        $this->bootConfig();
        $this->bootTranslations();
    }

    protected function getName(): string {
        return Package::Name;
    }
}
