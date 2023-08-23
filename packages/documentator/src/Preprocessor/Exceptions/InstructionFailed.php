<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions;

use Throwable;

abstract class InstructionFailed extends PreprocessFailed {
    public function __construct(
        private readonly string $path,
        private readonly string $target,
        string $message,
        Throwable $previous = null,
    ) {
        parent::__construct($message, $previous);
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getTarget(): string {
        return $this->target;
    }
}
