<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use Stringable;
use Throwable;

use function sprintf;

class OperatorImpossibleToCreateField extends BuilderException {
    public function __construct(
        protected Operator $operator,
        protected Stringable|string $source,
        protected Context $context,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Operator `%s`: Impossible to create field for `%s`.',
                $this->operator::class,
                $this->source,
            ),
            $previous,
        );
    }

    public function getOperator(): Operator {
        return $this->operator;
    }

    public function getSource(): Stringable|string {
        return $this->source;
    }

    public function getContext(): Context {
        return $this->context;
    }
}
