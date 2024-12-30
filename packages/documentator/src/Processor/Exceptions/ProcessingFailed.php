<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use Throwable;

class ProcessingFailed extends ProcessorError {
    public function __construct(?Throwable $previous = null) {
        parent::__construct('Processing failed.', $previous);
    }
}
