<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\AstManipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;

/**
 * Complex operator.
 *
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator
 */
interface ComplexOperator {
    public function getName(): string;

    public function getDefinition(
        AstManipulator $ast,
        InputObjectTypeDefinitionNode $node,
        string $prefix,
        bool $nullable,
    ): TypeDefinitionNode;

    /**
     * @param array<mixed> $conditions
     */
    public function apply(
        SearchBuilder $search,
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        array $conditions,
    ): EloquentBuilder|QueryBuilder;
}
