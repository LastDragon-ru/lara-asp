<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Utils\Instances;
use Override;

/**
 * @internal
 * @extends Instances<Instruction<Parameters>>
 */
class Instructions extends Instances {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getInstanceKeys(object|string $instance): array {
        return [$instance::getName()];
    }

    #[Override]
    protected function isHighPriorityFirst(): bool {
        return true;
    }
}
