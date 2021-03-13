<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use GraphQL\Language\Parser;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypesForScalar;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorNegationable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\ComparisonOperator;

class Between extends BaseOperator implements ComparisonOperator, OperatorNegationable, OperatorHasTypesForScalar {
    protected const TYPE_RANGE = 'Range';

    public function getName(): string {
        return 'between';
    }

    protected function getDescription(): string {
        return 'Within a range.';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, $map[self::TYPE_RANGE], true);
    }

    /**
     * @inheritdoc
     */
    public function getTypeDefinitionsForScalar(string $prefix, string $scalar): array {
        return [
            self::TYPE_RANGE => Parser::inputObjectTypeDefinition(
                /** @lang GraphQL */
                <<<GRAPHQL
                input {$prefix} {
                    min: {$scalar}!
                    max: {$scalar}!
                }
                GRAPHQL,
            ),
        ];
    }

    public function apply(
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        mixed $value,
        bool $not,
    ): EloquentBuilder|QueryBuilder {
        return $not
            ? $builder->whereNotBetween($property, $value)
            : $builder->whereBetween($property, $value);
    }
}
