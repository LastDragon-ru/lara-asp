<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use Throwable;

use function gettype;
use function sprintf;

class OperatorInvalidArgumentValue extends SearchByException {
    public function __construct(
        protected Operator $operator,
        protected string $expected,
        protected mixed $actual,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Operator `%s` expects `%s`, `%s` given.',
                $this->getOperator()::class,
                $this->getExpected(),
                gettype($this->getActual()),
            ),
            $previous,
        );
    }

    public function getOperator(): Operator {
        return $this->operator;
    }

    public function getExpected(): string {
        return $this->expected;
    }

    public function getActual(): mixed {
        return $this->actual;
    }
}
