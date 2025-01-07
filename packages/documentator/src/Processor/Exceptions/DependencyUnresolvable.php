<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use Throwable;

class DependencyUnresolvable extends DependencyError {
    public function __construct(
        /**
         * @var Dependency<*>
         */
        protected readonly Dependency $dependency,
        Throwable $previous,
    ) {
        parent::__construct('Dependency not found.', $previous);
    }

    /**
     * @return Dependency<*>
     */
    public function getDependency(): Dependency {
        return $this->dependency;
    }
}
