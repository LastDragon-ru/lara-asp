<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Flag;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

/**
 * @internal Must not be used directly.
 */
class IsNotNull extends Operator {
    #[Override]
    public static function getName(): string {
        return 'isNotNull';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Is NOT NULL?';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): string {
        return $provider->getType(Flag::class, $source, $context);
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

        $builder->whereNotNull($field);

        return $builder;
    }
}
