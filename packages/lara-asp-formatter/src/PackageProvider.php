<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithConfig;
use LastDragon_ru\LaraASP\Core\Provider\WithTranslations;
use Override;

class PackageProvider extends ServiceProvider {
    use WithConfig;
    use WithTranslations;

    #[Override]
    public function register(): void {
        parent::register();

        $this->registerConfig(PackageConfig::class);
        $this->app->scopedIf(Formatter::class);
    }

    public function boot(): void {
        $this->bootTranslations();
    }

    #[Override]
    protected function getName(): string {
        return Package::Name;
    }
}
