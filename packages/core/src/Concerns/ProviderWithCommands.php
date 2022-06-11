<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;

/**
 * @deprecated (v0.15.0) Laravel supports lazy commands since
 *      [v9](https://github.com/laravel/framework/pull/34873) and this
 *      trait/method is not needed.
 *
 * @mixin ServiceProvider
 */
trait ProviderWithCommands {
    /**
     * Define the commands when running in console.
     *
     * @param class-string<Command> ...$commands
     */
    protected function bootCommands(string ...$commands): void {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands($commands);
    }
}
