<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use Throwable;

abstract class InstructionFailed extends PreprocessError {
    public function __construct(
        private readonly Context $context,
        private readonly Parameters $parameters,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }

    public function getContext(): Context {
        return $this->context;
    }

    public function getParameters(): Parameters {
        return $this->parameters;
    }
}
