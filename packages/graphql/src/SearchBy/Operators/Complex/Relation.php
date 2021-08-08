<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Helpers\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\BuilderUnsupported;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;

use function is_array;
use function reset;

/**
 * @internal Must not be used directly.
 */
class Relation implements ComplexOperator {
    public function __construct() {
        // empty
    }

    public function getName(): string {
        return 'relation';
    }

    public function getDefinition(
        Manipulator $ast,
        InputValueDefinitionNode $field,
        InputObjectTypeDefinitionNode $type,
        string $name,
        bool $nullable,
    ): InputObjectTypeDefinitionNode {
        $count = $ast->getScalarType($ast->getScalarTypeNode(Directive::ScalarInt), false);
        $where = $ast->getInputType($type);

        return Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Conditions for the related objects (`has()`/`doesntHave()`) for input {$type->name->value}.

            See also:
            * https://laravel.com/docs/8.x/eloquent-relationships#querying-relationship-existence
            * https://laravel.com/docs/8.x/eloquent-relationships#querying-relationship-absence
            """
            input {$name} {
                """
                Additional conditions.
                """
                where: {$where}

                """
                Count conditions.
                """
                count: {$count}

                """
                Alias for `count: {greaterThanOrEqual: 1}` (`has()`). Will be ignored if `count` used.
                """
                exists: Boolean

                """
                Alias for `count: {lessThan: 1}` (`doesntHave()`). Will be ignored if `count` used.
                """
                notExists: Boolean! = false
            }
            DEF,
        );
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
            throw new BuilderUnsupported($builder::class);
        }

        // Possible variants:
        // * where                      = whereHas
        // * where + notExists          = doesntHave
        // * has + notExists + operator = error?

        // Conditions
        $relation  = (new ModelHelper($builder))->getRelation($property);
        $has       = $conditions['where'] ?? null;
        $notExists = (bool) ($conditions['notExists'] ?? false);

        // Build
        $alias    = $relation->getRelationCountHash(false);
        $count    = 1;
        $operator = '>=';

        if ($conditions['count'] ?? null) {
            $query    = $builder->toBase()->newQuery();
            $query    = $search->processComparison($query, 'tmp', $conditions['count']);
            $query    = $query instanceof EloquentBuilder ? $query->toBase() : $query;
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
