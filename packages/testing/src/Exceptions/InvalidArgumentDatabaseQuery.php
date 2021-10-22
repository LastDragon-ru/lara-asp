<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Exceptions;

use Throwable;

use function sprintf;

class InvalidArgumentDatabaseQuery extends InvalidArgument {
    public function __construct(
        protected string $argument,
        protected mixed $value,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Argument `%1$s` must be Database query, `%2$s` given.',
            $this->argument,
            $this->getType($this->value),
        ), 0, $previous);
    }
}
