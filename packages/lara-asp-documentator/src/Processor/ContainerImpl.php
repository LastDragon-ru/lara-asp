<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Container;
use Override;

class ContainerImpl implements Container {
    public function __construct(
        protected ContainerResolver $container,
    ) {
        // empty
    }

    #[Override]
    public function make(string $class): object {
        return $this->container->getInstance()->make($class);
    }
}
