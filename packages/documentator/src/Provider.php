<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithViews;
use LastDragon_ru\LaraASP\Documentator\Commands\Commands;
use LastDragon_ru\LaraASP\Documentator\Commands\Preprocess;
use LastDragon_ru\LaraASP\Documentator\Commands\Requirements;
use Override;

class Provider extends ServiceProvider {
    use WithViews;

    public function boot(): void {
        $this->bootViews();
        $this->commands(
            Requirements::class,
            Preprocess::class,
            Commands::class,
        );
    }

    #[Override]
    protected function getName(): string {
        return Package::Name;
    }
}
