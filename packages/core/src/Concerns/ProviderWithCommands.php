<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

/**
 * @mixin \Illuminate\Support\ServiceProvider
 */
trait ProviderWithCommands {
    /**
     * Define the commands.
     *
     * @param class-string<\Illuminate\Console\Command> $commands
     */
    protected function bootCommands(string ...$commands): void {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands($commands);
    }
}
