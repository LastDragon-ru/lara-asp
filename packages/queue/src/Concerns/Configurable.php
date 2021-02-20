<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use LastDragon_ru\LaraASP\Queue\QueueableConfigurator;
use RuntimeException;

use function sprintf;

trait Configurable {
    public function __construct(QueueableConfigurator $configurator) {
        if (!($this instanceof ConfigurableQueueable)) {
            throw new RuntimeException(sprintf('Class must implement %s.', ConfigurableQueueable::class));
        }

        $configurator->configure($this);
    }

    /**
     * @return array<string,mixed>
     */
    public function getQueueConfig(): array {
        return [];
    }
}
