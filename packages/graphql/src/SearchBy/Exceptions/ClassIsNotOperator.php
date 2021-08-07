<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use Throwable;

use function sprintf;

class ClassIsNotOperator extends SearchByException {
    /**
     * @param class-string $class
     */
    public function __construct(
        protected string $class,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Class `%s` must implement `%s`.',
            $this->class,
            Operator::class,
        ), $previous);
    }

    /**
     * @return class-string
     */
    public function getClass(): string {
        return $this->class;
    }
}
