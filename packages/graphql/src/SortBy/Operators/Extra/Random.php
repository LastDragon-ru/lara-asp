<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Extra;

use GraphQL\Language\DirectiveLocation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Operator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Flag;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

class Random extends Operator {
    // <editor-fold desc="Directive">
    // =========================================================================
    /**
     * @inheritDoc
     */
    #[Override]
    protected static function getLocations(): array {
        return [
            DirectiveLocation::SCALAR,
        ];
    }
    // </editor-fold>

    // <editor-fold desc="Operator">
    // =========================================================================
    #[Override]
    public static function getName(): string {
        return 'random';
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return 'By random';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): ?string {
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

        $builder->inRandomOrder();

        return $builder;
    }
    // </editor-fold>
}
