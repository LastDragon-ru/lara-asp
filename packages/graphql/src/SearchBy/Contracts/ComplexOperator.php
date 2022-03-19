<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;

/**
 * Complex operator.
 *
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinitionProvider
 */
interface ComplexOperator {
    public function getName(): string;

    public function getDefinition(
        Manipulator $ast,
        InputValueDefinitionNode|InputObjectField $field,
        InputObjectTypeDefinitionNode|InputObjectType $type,
        string $name,
        bool $nullable,
    ): InputObjectTypeDefinitionNode;

    /**
     * @param EloquentBuilder<Model>|QueryBuilder $builder
     * @param array<mixed>                        $conditions
     *
     * @return EloquentBuilder<Model>|QueryBuilder
     */
    public function apply(
        SearchBuilder $search,
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        array $conditions,
    ): EloquentBuilder|QueryBuilder;
}
