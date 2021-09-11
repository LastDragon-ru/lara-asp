<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;

class Provider extends ServiceProvider {
    public function register(): void {
        parent::register();

        $this->registerConfigurator();
    }

    protected function registerConfigurator(): void {
        $this->app->afterResolving(
            ConfigurableQueueable::class,
            static function (ConfigurableQueueable $queueable, Container $container): void {
                $container->make(QueueableConfigurator::class)->configure($queueable);
            },
        );
    }

    protected function getName(): string {
        return Package::Name;
    }
}
