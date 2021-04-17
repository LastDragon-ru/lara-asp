<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Helpers\ModelHelper;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\AstManipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchLogicException;

use function is_array;
use function reset;

/**
 * @internal Must not be used directly.
 */
class Relation implements ComplexOperator {
    public function __construct(
        protected PackageTranslator $translator,
    ) {
        // empty
    }

    public function getName(): string {
        return 'relation';
    }

    public function getDefinition(
        AstManipulator $ast,
        InputValueDefinitionNode $field,
        InputObjectTypeDefinitionNode $type,
        string $prefix,
        bool $nullable,
    ): TypeDefinitionNode {
        $count = $ast->getScalarType($ast->getScalarTypeNode(Directive::ScalarInt), false);
        $where = $ast->getInputType($type);

        return Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Conditions for the related objects (`has()`) for input {$type->name->value}.

            See also:
            * https://laravel.com/docs/8.x/eloquent-relationships#querying-relationship-existence
            * https://laravel.com/docs/8.x/eloquent-relationships#querying-relationship-absence
            """
            input {$prefix} {
                {$this->getName()}: SearchByFlag! = yes

                where: {$where}

                count: {$count}

                """
                Shortcut for `doesntHave()`, same as:

                ```
                where: [...]
                count: {
                  lt: 1
                }
                ```
                """
                not: Boolean! = false
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
        $has      = $conditions['where'] ?? null;
        $not      = (bool) ($conditions['not'] ?? false);

        // Build
        $alias    = $relation->getRelationCountHash(false);
        $count    = 1;
        $operator = '>=';

        if ($conditions['count'] ?? null) {
            $query    = $builder->toBase()->newQuery();
            $query    = $search->processComparison($query, 'tmp', $conditions['count']);
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
