<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Utils\Instances;
use LastDragon_ru\LaraASP\Documentator\Processor\Utils\InstancesOrder;

/**
 * @internal
 * @extends Instances<Instruction<Parameters>>
 */
class Instructions extends Instances {
    public function __construct(Container $container) {
        parent::__construct($container, InstancesOrder::Desc);
    }
}
