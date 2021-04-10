<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Helpers\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchLogicException;

use function is_array;
use function reset;

/**
 * @internal Must not be used directly.
 */
class Relation implements Operator, ComplexOperator {
    public function __construct(
        protected PackageTranslator $translator,
    ) {
        // empty
    }

    public function getName(): string {
        return 'where';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return <<<DEF
        """
        Conditions for the related objects (`has()`).

        See also:
        * https://laravel.com/docs/8.x/eloquent-relationships#querying-relationship-existence
        * https://laravel.com/docs/8.x/eloquent-relationships#querying-relationship-absence
        """
        {$this->getName()}: {$scalar}!

        """
        Shortcut for `doesntHave()`, same as:

        ```
        where: [...]
        lt: 1
        ```
        """
        not: Boolean! = false
        DEF;
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
            throw new SearchLogicException($this->translator->get(
                'search_by.errors.unsupported_option',
                [
                    'operator' => $this->getName(),
                    'option'   => QueryBuilder::class,
                ],
            ));
        }

        // Possible variants:
        // * where                = whereHas
        // * where + not          = doesntHave
        // * has + not + operator = error

        // Conditions & Not
        $relation = (new ModelHelper($builder))->getRelation($property);
        $original = $conditions;
        $has      = $conditions[$this->getName()];
        $not      = (bool) ($conditions['not'] ?? false);

        unset($conditions[$this->getName()]);
        unset($conditions['not']);
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
                $relation,
                $search,
                $alias,
                $has,
            ): EloquentBuilder|QueryBuilder {
                if ($alias === $relation->getRelationCountHash(false)) {
                    $alias = null;
                }

                return is_array($has)
                    ? $search->process($builder, $has, $alias)
                    : $builder;
            },
            $operator,
            $count,
        );
    }
}
