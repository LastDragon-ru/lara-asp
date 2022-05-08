<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use Closure;
use Exception;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\Eloquent\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Builder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorInvalidArguments;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;

use function reset;

class Relation extends BaseOperator implements ComplexOperator {
    public static function getName(): string {
        return 'relation';
    }

    public function getFieldDescription(): string {
        return 'Relationship condition.';
    }

    public function getDefinition(
        Manipulator $ast,
        InputValueDefinitionNode|InputObjectField $field,
        InputObjectTypeDefinitionNode|InputObjectType $type,
        string $name,
        bool $nullable,
    ): InputObjectTypeDefinitionNode {
        $count = $ast->getScalarType($ast->getScalarTypeNode(Directive::ScalarInt), false);
        $where = $ast->getInputType($type);

        return Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Conditions for the related objects (`has()`/`doesntHave()`) for input {$ast->getNodeName($type)}.

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
                Alias for `count: {greaterThanOrEqual: 1}`. Will be ignored if `count` used.
                """
                exists: Boolean

                """
                Alias for `count: {lessThan: 1}`. Will be ignored if `count` used.
                """
                notExists: Boolean! = false
            }
            DEF,
        );
    }

    public function isBuilderSupported(object $builder): bool {
        return $builder instanceof EloquentBuilder;
    }

    public function call(Builder $search, object $builder, Property $property, Argument $argument): object {
        // Supported?
        if (!($builder instanceof EloquentBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        // ArgumentSet?
        if (!($argument->value instanceof ArgumentSet)) {
            throw new OperatorInvalidArguments($this, ArgumentSet::class, $argument->value);
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
            $query    = $search->where($query, $hasCount, new Property('tmp'));
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
        return $this->build(
            $builder,
            $property,
            $operator,
            $count,
            static function (
                EloquentBuilder $builder,
            ) use (
                $relation,
                $search,
                $alias,
                $has,
            ): EloquentBuilder|QueryBuilder {
                if ($alias === $relation->getRelationCountHash(false)) {
                    $alias = $builder->getModel()->getTable();
                }

                return $has instanceof Argument && $has->value instanceof ArgumentSet
                    ? $search->where($builder, $has->value, new Property($alias))
                    : $builder;
            },
        );
    }

    /**
     * @inheritDoc
     */
    public function apply(
        SearchBuilder $search,
        EloquentBuilder|QueryBuilder $builder,
        string $property,
        array $conditions,
    ): EloquentBuilder|QueryBuilder {
        throw new Exception('deprecated');
    }

    /**
     * @param EloquentBuilder<Model> $builder
     * @param Closure(EloquentBuilder<Model>): (EloquentBuilder<Model>|QueryBuilder) $closure
     *
     * @return EloquentBuilder<Model>
     */
    protected function build(
        EloquentBuilder $builder,
        Property $property,
        string $operator,
        int $count,
        Closure $closure,
    ): EloquentBuilder {
        return $builder->whereHas((string) $property, $closure, $operator, $count);
    }
}
