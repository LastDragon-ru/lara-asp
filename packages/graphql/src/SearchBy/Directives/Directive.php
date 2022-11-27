<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FailedToCreateSearchCondition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Condition;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Scout\ScoutBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function str_starts_with;

class Directive extends HandlerDirective implements ArgManipulator, ArgBuilderDirective, ScoutBuilderDirective {
    public const Name = 'SearchBy';

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Use Input as Search Conditions for the current Builder.
            """
            directive @searchBy on ARGUMENT_DEFINITION
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
            Condition::class,
        );

        if (!$type) {
            throw new FailedToCreateSearchCondition($argDefinition->name->value);
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
    public function handleBuilder($builder, $value): EloquentBuilder|QueryBuilder {
        return $this->handleAnyBuilder($builder, $value);
    }

    public function handleScoutBuilder(ScoutBuilder $builder, mixed $value): ScoutBuilder {
        return $this->handleAnyBuilder($builder, $value);
    }

    public function handle(object $builder, Property $property, ArgumentSet $conditions): object {
        // Some relations (eg `HasManyThrough`) require a table name prefix to
        // avoid "SQLSTATE[23000]: Integrity constraint violation: 1052 Column
        // 'xxx' in where clause is ambiguous" error.
        if ($builder instanceof EloquentBuilder && $property->getPath() === []) {
            $property = $property->getChild($builder->getModel()->getTable());
        }

        // Return
        return parent::handle($builder, $property, $conditions);
    }
    // </editor-fold>
}
