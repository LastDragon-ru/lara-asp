<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Exceptions;

use Throwable;

use function sprintf;

class InvalidArgumentJsonSchema extends InvalidArgument {
    public function __construct(
        protected string $argument,
        protected mixed $value,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Argument `%1$s` must be a valid JSON Schema.',
            $this->argument,
        ), 0, $previous);
    }
}
