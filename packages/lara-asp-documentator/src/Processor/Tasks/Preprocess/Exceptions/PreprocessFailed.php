<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions;

use Throwable;

class PreprocessFailed extends PreprocessError {
    public function __construct(
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            'Preprocessing failed.',
            $previous,
        );
    }
}
