<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComparisonOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\LogicalOperator;

use function array_keys;
use function count;
use function implode;
use function key;
use function reset;

class SearchBuilder {
    /**
     * @var array<string, \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator>
     */
    protected array $complex = [];

    /**
     * @var array<string, \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\LogicalOperator>
     */
    protected array $logical = [];

    /**
     * @var array<string, \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComparisonOperator>
     */
    protected array $comparison = [];

    /**
     * @param array<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComparisonOperator
     *      |\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\LogicalOperator
     *      |\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator> $operators
     */
    public function __construct(
        protected PackageTranslator $translator,
        array $operators,
    ) {
        foreach ($operators as $operator) {
            if ($operator instanceof ComparisonOperator) {
                $this->comparison[$operator->getName()] = $operator;
            } elseif ($operator instanceof LogicalOperator) {
                $this->logical[$operator->getName()] = $operator;
            } elseif ($operator instanceof ComplexOperator) {
                $this->complex[$operator->getName()] = $operator;
            } else {
                throw new InvalidArgumentException(
                    'Unsupported operator type.',
                );
            }
        }
    }

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * @param array<mixed> $conditions
     */
    public function build(EloquentBuilder|QueryBuilder $builder, array $conditions): EloquentBuilder|QueryBuilder {
        return $this->process($builder, $conditions);
    }
    // </editor-fold>

    // <editor-fold desc="Process">
    // =========================================================================
    /**
     * @param array<mixed> $input
     */
    public function process(
        EloquentBuilder|QueryBuilder $builder,
        array $input,
        string $tableAlias = null,
    ): EloquentBuilder|QueryBuilder {
        // More than one property?
        if (count($input) > 1) {
            throw new SearchLogicException($this->translator->get(
                'search_by.errors.too_many_properties',
                [
                    'properties' => implode('`, `', array_keys($input)),
                ],
            ));
        }

        // On this level, each item can be one of the following
        // - one of the logical operators
        // - complex condition
        // - property condition
        foreach ($input as $property => $conditions) {
            // Complex?
            $complex = $this->getComplexOperator($conditions);

            if ($complex) {
                $builder = $this->processComplexOperator($builder, $complex, $property, $conditions);

                continue;
            }

            // Logical operator?
            $logical = $this->getLogicalOperator($property);

            if ($logical) {
                $builder = $this->processLogicalOperator($builder, $logical, $conditions, $tableAlias);

                continue;
            }

            // Comparison
            $this->processComparison($builder, $property, $conditions, $tableAlias);
        }

        return $builder;
    }

    /**
     * @param array<mixed> $conditions
     */
    public function processComplexOperator(
        EloquentBuilder|QueryBuilder $builder,
        ComplexOperator $complex,
        string $property,
        array $conditions,
    ): EloquentBuilder|QueryBuilder {
        return $builder->where(
            function (
                EloquentBuilder|QueryBuilder $builder,
            ) use (
                $property,
                $complex,
                $conditions,
            ): EloquentBuilder|QueryBuilder {
                return $complex->apply($this, $builder, $property, $conditions);
            },
        );
    }

    /**
     * @param array<mixed> $conditions
     */
    public function processLogicalOperator(
        EloquentBuilder|QueryBuilder $builder,
        LogicalOperator $logical,
        array $conditions,
        string $tableAlias = null,
    ): EloquentBuilder|QueryBuilder {
        return $builder->where(
            function (EloquentBuilder|QueryBuilder $builder) use (
                $logical,
                $conditions,
                $tableAlias,
            ): EloquentBuilder|QueryBuilder {
                return $logical->apply($this, $builder, $conditions, $tableAlias);
            },
        );
    }

    /**
     * @param array<mixed> $conditions
     */
    public function processComparison(
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        array $conditions,
        string $tableAlias = null,
    ): EloquentBuilder|QueryBuilder {
        // Empty?
        if (count($conditions) <= 0) {
            throw new SearchLogicException($this->translator->get(
                'search_by.errors.empty_condition',
            ));
        }

        // More than one operator?
        if (count($conditions) > 1) {
            throw new SearchLogicException($this->translator->get(
                'search_by.errors.too_many_operators',
                [
                    'operators' => implode('`, `', array_keys($conditions)),
                ],
            ));
        }

        // Get Operator
        $name     = (string) key($conditions);
        $value    = reset($conditions);
        $operator = $this->getComparisonOperator($name);

        // Found?
        if (!$operator) {
            throw new SearchLogicException($this->translator->get(
                'search_by.errors.unknown_operator',
                [
                    'operator' => $name,
                ],
            ));
        }

        // Table Alias?
        if ($tableAlias) {
            $property = "{$tableAlias}.{$property}";
        } elseif ($builder instanceof EloquentBuilder) {
            $property = $builder->qualifyColumn($property);
        } else {
            // empty
        }

        // Apply
        return $operator->apply($builder, $property, $value);
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @param array<mixed> $conditions
     */
    public function getComplexOperator(array $conditions): ?ComplexOperator {
        $complex = null;

        foreach ($this->complex as $name => $operator) {
            if (isset($conditions[$name])) {
                $complex = $operator;
                break;
            }
        }

        return $complex;
    }

    public function getLogicalOperator(string $property): ?LogicalOperator {
        return $this->logical[$property] ?? null;
    }

    public function getComparisonOperator(string $property): ?ComparisonOperator {
        return $this->comparison[$property] ?? null;
    }
    // </editor-fold>
}
