<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

class NotBetween extends Between {
    #[Override]
    public static function getName(): string {
        return 'notBetween';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Outside a range.';
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
        $value = Cast::toIterable($argument->toPlain());

        $builder->whereNotBetween($field, $value);

        return $builder;
    }
}
