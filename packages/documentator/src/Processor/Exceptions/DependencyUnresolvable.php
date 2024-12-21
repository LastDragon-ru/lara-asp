<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use Throwable;

use function sprintf;

class DependencyUnresolvable extends DependencyError {
    public function __construct(
        /**
         * @var Dependency<*>
         */
        protected readonly Dependency $dependency,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Dependency `%s` not found.',
                $this->dependency->getPath(),
            ),
            $previous,
        );
    }

    /**
     * @return Dependency<*>
     */
    public function getDependency(): Dependency {
        return $this->dependency;
    }
}
