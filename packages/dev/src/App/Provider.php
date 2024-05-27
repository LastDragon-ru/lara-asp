<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\App;

use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample\Contracts\Runner;
use Override;

class Provider extends ServiceProvider {
    #[Override]
    public function register(): void {
        parent::register();

        $this->app->bind(Runner::class, Example::class);
    }
}
