<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithConfig;
use LastDragon_ru\LaraASP\Core\Provider\WithRoutes;
use LastDragon_ru\LaraASP\Core\Provider\WithTranslations;
use Override;

class Provider extends ServiceProvider {
    use WithConfig;
    use WithRoutes;
    use WithTranslations;

    // <editor-fold desc="\Illuminate\Support\ServiceProvider">
    // =========================================================================
    public function boot(): void {
        $this->bootConfig();
        $this->bootRoutes();
        $this->bootTranslations();
    }
    // </editor-fold>

    // <editor-fold desc="Functions">
    // =========================================================================
    #[Override]
    protected function getName(): string {
        return Package::Name;
    }
    // </editor-fold>
}
