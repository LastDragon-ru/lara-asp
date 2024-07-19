<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use Throwable;

abstract class InstructionFailed extends PreprocessError {
    public function __construct(
        private readonly Context $context,
        string $message,
        Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }

    public function getContext(): Context {
        return $this->context;
    }
}
