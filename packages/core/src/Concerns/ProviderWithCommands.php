<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;

/**
 * @mixin ServiceProvider
 */
trait ProviderWithCommands {
    /**
     * Define the commands.
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
