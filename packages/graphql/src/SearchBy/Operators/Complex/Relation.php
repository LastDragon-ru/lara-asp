<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorNegationable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchLogicException;

use function is_array;
use function reset;
use function sprintf;

/**
 * @internal Must not be used directly.
 */
class Relation extends BaseOperator implements ComplexOperator, OperatorNegationable {
    public function getName(): string {
        return 'has';
    }

    protected function getDescription(): string {
        return 'Has?';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, "{$scalar}!", true);
    }

    /**
     * @inheritdoc
     */
    public function apply(
        SearchBuilder $search,
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        array $conditions,
    ): EloquentBuilder {
        // QueryBuilder?
        if ($builder instanceof QueryBuilder) {
            throw new SearchLogicException(sprintf(
                'Operator `%s` can not be used with `%s`.',
                $this->getName(),
                QueryBuilder::class,
            ));
        }

        // Possible variants:
        // * has + not            = doesntHave
        // * has + not + operator = has + !$operator

        // Conditions & Not
        $relation = (new ModelHelper($builder))->getRelation($property);
        $original = $conditions;
        $has      = $conditions[$this->getName()];
        $not      = (bool) $search->getNotOperator($conditions);

        unset($conditions[$this->getName()]);
        unset($original[$this->getName()]);

        // Build
        $alias    = $relation->getRelationCountHash(false);
        $count    = 1;
        $operator = '>=';

        if ($conditions) {
            $query    = $builder->toBase()->newQuery();
            $query    = $search->processComparison($query, 'tmp', $original);
            $where    = reset($query->wheres);
            $count    = $where['value'] ?? $count;
            $operator = $where['operator'] ?? $operator;
        } elseif ($not) {
            $count    = 1;
            $operator = '<';
        } else {
            // empty
        }

        // Build
        return $builder->whereHas(
            $property,
            static function (
                EloquentBuilder|QueryBuilder $builder,
            ) use (
                $search,
                $alias,
                $has,
            ): EloquentBuilder|QueryBuilder {
                return is_array($has)
                    ? $search->process($builder, $has, $alias)
                    : $builder;
            },
            $operator,
            $count,
        );
    }
}
