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
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Flag;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

use function array_merge;
use function is_a;

class Random extends BaseOperator {
    // <editor-fold desc="Directive">
    // =========================================================================
    /**
     * @inheritDoc
     */
    #[Override]
    protected static function getDirectiveLocations(): array {
        return array_merge(parent::getDirectiveLocations(), [
            DirectiveLocation::FIELD_DEFINITION,
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Operator">
    // =========================================================================
    #[Override]
    public static function getName(): string {
        return 'random';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'By random';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return $provider->getType(Flag::class, $source);
    }

    #[Override]
    public function isBuilderSupported(string $builder): bool {
        return is_a($builder, EloquentBuilder::class, true)
            || is_a($builder, QueryBuilder::class, true);
    }

    #[Override]
    public function call(
        Handler $handler,
        Context $context,
        object $builder,
        Property $property,
        Argument $argument,
    ): object {
        if (!($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        $builder->inRandomOrder();

        return $builder;
    }
    // </editor-fold>
}
