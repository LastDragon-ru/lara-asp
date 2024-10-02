<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Contracts\Runner;
use Override;

class Provider extends ServiceProvider {
    #[Override]
    public function register(): void {
        parent::register();

        $this->app->bind(Runner::class, Example::class);
    }
}
