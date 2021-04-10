<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison;

use GraphQL\Language\Parser;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypesForScalar;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;

class Between extends BaseOperator implements ComparisonOperator, OperatorHasTypesForScalar {
    protected const TypeRange = 'Range';

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
        return parent::getDefinition($map, $map[self::TypeRange], true);
    }

    /**
     * @inheritdoc
     */
    public function getTypeDefinitionsForScalar(string $prefix, string $scalar): array {
        return [
            self::TypeRange => Parser::inputObjectTypeDefinition(
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
    ): EloquentBuilder|QueryBuilder {
        return $builder->whereBetween($property, $value);
    }
}
