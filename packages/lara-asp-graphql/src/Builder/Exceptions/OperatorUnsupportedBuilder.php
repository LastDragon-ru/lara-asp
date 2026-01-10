<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use Throwable;

use function sprintf;

class OperatorUnsupportedBuilder extends BuilderException {
    public function __construct(
        protected Operator $operator,
        protected object $builder,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Operator `%s` does not support `%s` builder.',
                $this->operator::class,
                $this->builder::class,
            ),
            $previous,
        );
    }

    public function getOperator(): Operator {
        return $this->operator;
    }

    public function getBuilder(): object {
        return $this->builder;
    }
}
