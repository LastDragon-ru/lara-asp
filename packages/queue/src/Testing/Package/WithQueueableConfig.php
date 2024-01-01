<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Testing\Package;

use LastDragon_ru\LaraASP\Core\Utils\ConfigMerger;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;

use function config;
use function is_object;
use function sprintf;

trait WithQueueableConfig {
    /**
     * @param ConfigurableQueueable|class-string<ConfigurableQueueable> $queueable
     * @param array<string, mixed>                                      $settings
     */
    private function setQueueableConfig(ConfigurableQueueable|string $queueable, array $settings): void {
        $key    = sprintf('queue.queueables.%s', is_object($queueable) ? $queueable::class : $queueable);
        $target = [ConfigMerger::Strict => false] + (array) config($key);
        $merger = new ConfigMerger();

        config([
            $key => $merger->merge($target, $settings),
        ]);
    }
}
