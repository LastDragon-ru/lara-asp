<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\PackageException;
use Throwable;

class PreprocessingFailed extends PackageException {
    public function __construct(
        Throwable $previous = null,
    ) {
        parent::__construct(
            'Preprocessing failed.',
            $previous,
        );
    }
}
