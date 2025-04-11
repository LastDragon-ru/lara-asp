<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use Throwable;

class DependencyUnresolvable extends DependencyError {
    public function __construct(Throwable $previous) {
        parent::__construct('Dependency not found.', $previous);
    }
}
