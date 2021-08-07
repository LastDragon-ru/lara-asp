<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Throwable;

use function sprintf;

class ComplexOperatorInvalidTypeName extends SearchByException {
    public function __construct(
        protected string $operator,
        protected string $expected,
        protected string $actual,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Generated type for complex operator `%s` must be named as `%s`, but its name is `%s`.',
            $this->operator,
            $this->expected,
            $this->actual,
        ), $previous);
    }

    public function getOperator(): string {
        return $this->operator;
    }

    public function getExpected(): string {
        return $this->expected;
    }

    public function getActual(): string {
        return $this->actual;
    }
}
