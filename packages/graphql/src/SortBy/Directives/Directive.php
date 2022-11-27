<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedToCreateSortClause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Clause;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Scout\ScoutBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function str_starts_with;

class Directive extends HandlerDirective implements ArgManipulator, ArgBuilderDirective, ScoutBuilderDirective {
    public const Name = 'SortBy';

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Convert Input into Sort Clause.
            """
            directive @sortBy on ARGUMENT_DEFINITION
        GRAPHQL;
    }

    // <editor-fold desc="Manipulate">
    // =========================================================================
    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode &$parentType,
    ): void {
        $type = $this->getArgumentTypeDefinitionNode(
            $documentAST,
            $argDefinition,
            $parentField,
            Clause::class,
        );

        if (!$type) {
            throw new FailedToCreateSortClause($argDefinition->name->value);
        }

        $argDefinition->type = $type;
    }

    protected function isTypeName(string $name): bool {
        return str_starts_with($name, Directive::Name);
    }
    // </editor-fold>

    // <editor-fold desc="Handle">
    // =========================================================================
    /**
     * @inheritDoc
     * @return EloquentBuilder<Model>|QueryBuilder
     */
    public function handleBuilder($builder, mixed $value): EloquentBuilder|QueryBuilder {
        return $this->handleAnyBuilder($builder, $value);
    }

    public function handleScoutBuilder(ScoutBuilder $builder, mixed $value): ScoutBuilder {
        return $this->handleAnyBuilder($builder, $value);
    }
    // </editor-fold>
}
