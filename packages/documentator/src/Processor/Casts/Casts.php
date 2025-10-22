<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Utils\Instances;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;

/**
 * @internal
 * @extends Instances<Cast<object>>
 */
class Casts extends Instances {
    public function __construct(ContainerResolver $container) {
        parent::__construct($container, SortOrder::Desc);
    }
}
