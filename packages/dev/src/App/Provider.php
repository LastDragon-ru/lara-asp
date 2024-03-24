<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\App;

use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider {
    public function boot(): void {
        $this->commands(
            Directive::class,
            Example::class,
        );
    }
}
