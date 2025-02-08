<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Utils\Instances;
use Override;

/**
 * @internal
 * @extends Instances<Task>
 */
class Tasks extends Instances {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getInstanceKeys(object|string $instance): array {
        return $instance::getExtensions();
    }
}
