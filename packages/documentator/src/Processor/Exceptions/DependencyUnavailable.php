<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use Throwable;

class DependencyUnavailable extends DependencyError {
    public function __construct(?Throwable $previous = null) {
        parent::__construct('Dependency is not available.', $previous);
    }
}
