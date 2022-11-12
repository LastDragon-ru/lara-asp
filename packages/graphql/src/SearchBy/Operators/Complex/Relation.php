<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use LastDragon_ru\LaraASP\Eloquent\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorInvalidArgumentValue;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Property as PropertyOperator;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;

use function reset;

class Relation extends BaseOperator {
    public function __construct(
        protected PropertyOperator $property,
    ) {
        parent::__construct();
    }

    // <editor-fold desc="Operator">
    // =========================================================================
    public static function getName(): string {
        return 'relation';
    }

    public function getFieldType(TypeProvider $provider, string $type): string {
        return $provider->getType(RelationType::class, $type);
    }

    public function getFieldDescription(): string {
        return 'Relationship condition.';
    }

    public function isBuilderSupported(object $builder): bool {
        return $builder instanceof EloquentBuilder;
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        // Supported?
        if (!($builder instanceof EloquentBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        // ArgumentSet?
        if (!($argument->value instanceof ArgumentSet)) {
            throw new OperatorInvalidArgumentValue($this, ArgumentSet::class, $argument->value);
        }

        // Possible variants:
        // * where              = whereHas
        // * where + count      = whereHas
        // * where + exists     = whereHas
        // * where + notExists  = doesntHave

        // Conditions
        $relation  = (new ModelHelper($builder))->getRelation($property->getName());
        $has       = $argument->value->arguments['where'] ?? null;
        $hasCount  = $argument->value->arguments['count'] ?? null;
        $notExists = (bool) ($argument->value->arguments['notExists']->value ?? false);

        // Build
        $alias    = $relation->getRelationCountHash(false);
        $count    = 1;
        $operator = '>=';

        if ($hasCount instanceof Argument) {
            $query    = $builder->toBase()->newQuery();
            $query    = $this->property->call($handler, $query, new Property(), $hasCount);
            $where    = reset($query->wheres);
            $count    = $where['value'] ?? $count;
            $operator = $where['operator'] ?? $operator;
        } elseif ($notExists) {
            $count    = 1;
            $operator = '<';
        } else {
            // empty
        }

        // Build
        $this->build(
            $builder,
            $property,
            $operator,
            $count,
            static function (EloquentBuilder $builder) use ($relation, $handler, $alias, $has): void {
                if (!$alias || $alias === $relation->getRelationCountHash(false)) {
                    $alias = $builder->getModel()->getTable();
                }

                if ($has instanceof Argument && $has->value instanceof ArgumentSet) {
                    $handler->handle($builder, new Property($alias), $has->value);
                }
            },
        );

        // Return
        return $builder;
    }

    /**
     * @template TBuilder of EloquentBuilder<\Illuminate\Database\Eloquent\Model>
     *
     * @param TBuilder                $builder
     * @param Closure(TBuilder): void $closure
     */
    protected function build(
        EloquentBuilder $builder,
        Property $property,
        string $operator,
        int $count,
        Closure $closure,
    ): void {
        $builder->whereHas($property->getName(), $closure, $operator, $count);
    }
}
