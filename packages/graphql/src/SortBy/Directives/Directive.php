<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
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

    protected function getArgumentTypeDefinitionNode(
        DocumentAST $document,
        InputValueDefinitionNode $argument,
        FieldDefinitionNode $field,
        string $operator,
    ): ListTypeNode|NamedTypeNode|NonNullTypeNode|null {
        // Converted?
        /** @var Manipulator $manipulator */
        $manipulator = $this->getContainer()->make(Manipulator::class, [
            'document'    => $document,
            'builderInfo' => $this->getBuilderInfo($field),
        ]);

        if ($this->isTypeName($manipulator->getNodeTypeName($argument))) {
            return $argument->type;
        }

        // Convert
        $type        = null;
        $definition  = $manipulator->isPlaceholder($argument)
            ? $manipulator->getPlaceholderTypeDefinitionNode($field)
            : $manipulator->getTypeDefinitionNode($argument);
        $isSupported = $definition instanceof InputObjectTypeDefinitionNode
            || $definition instanceof ObjectTypeDefinitionNode
            || $definition instanceof InputObjectType
            || $definition instanceof ObjectType;

        if ($isSupported) {
            $operator = $manipulator->getOperator(static::getScope(), $operator);
            $name     = $manipulator->getNodeTypeName($definition);
            $type     = $operator->getFieldType($manipulator, $name, $manipulator->isNullable($argument));
            $type     = Parser::typeReference($type);
        }

        // Return
        return $type;
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
