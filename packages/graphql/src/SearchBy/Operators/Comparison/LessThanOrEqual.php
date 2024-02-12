<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Operator;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

class LessThanOrEqual extends Operator {
    #[Override]
    public static function getName(): string {
        return 'lessThanOrEqual';
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return 'Less than or equal to (`<=`).';
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Field $field,
        Argument $argument,
        Context $context,
    ): object {
        if (!($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        $field = $this->resolver->getField($builder, $field->getParent());
        $value = $argument->toPlain();

        $builder->where($field, '<=', $value);

        return $builder;
    }
}
