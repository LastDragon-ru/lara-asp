<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Exceptions;

use Throwable;

use function sprintf;

class InvalidArgumentClass extends InvalidArgument {
    public function __construct(
        protected string $argument,
        protected string $class,
        ?Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Argument `%1$s` must be a class with known path, `%2$s` given.',
            $this->argument,
            $this->class,
        ), 0, $previous);
    }
}
