<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function array_keys;
use function count;
use function implode;
use function key;
use function reset;
use function sprintf;

class SortByDirective extends BaseDirective implements ArgManipulator, ArgBuilderDirective {
    public const Name          = 'SortBy';
    public const TypeDirection = 'Direction';

    public function __construct() {
        // empty
    }

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Convert Input into Sort Clause.
            """
            directive @sortBy on INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode &$parentType,
    ): void {
        $argDefinition->type = (new AstManipulator(
            $documentAST,
            self::Name,
        ))->getType($argDefinition);
    }

    /**
     * @inheritdoc
     */
    public function handleBuilder($builder, $value): QueryBuilder|EloquentBuilder {
        foreach ((array) $value as $clause) {
            $builder = $this->processClause($builder, $clause);
        }

        return $builder;
    }

    /**
     * @param array<string, string> $clause
     */
    protected function processClause(
        EloquentBuilder|QueryBuilder $builder,
        array $clause,
    ): QueryBuilder|EloquentBuilder {
        // Empty?
        if (!$clause) {
            throw new SortLogicException(
                'Sort clause cannot be empty.',
            );
        }

        // More than one property?
        if (count($clause) > 1) {
            throw new SortLogicException(sprintf(
                'Only one property allowed, found: %s.',
                '`'.implode('`, `', array_keys($clause)).'`',
            ));
        }

        // Apply
        $direction = reset($clause);
        $column    = key($clause);

        $builder->orderBy($column, $direction);

        // Return
        return $builder;
    }
}
