<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use Override;

class Provider extends ServiceProvider {
    #[Override]
    public function register(): void {
        parent::register();

        $this->registerConfigurator();
    }

    protected function registerConfigurator(): void {
        $this->callAfterResolving(
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
