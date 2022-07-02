<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Manipulator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

class Directive extends HandlerDirective implements ArgManipulator, ArgBuilderDirective {
    public const Name          = 'SearchBy';
    public const ScalarID      = 'ID';
    public const ScalarInt     = 'Int';
    public const ScalarFloat   = 'Float';
    public const ScalarString  = 'String';
    public const ScalarBoolean = 'Boolean';
    public const ScalarEnum    = self::Name.'Enum';
    public const ScalarNull    = self::Name.'Null';
    public const ScalarLogic   = self::Name.'Logic';
    public const ScalarNumber  = self::Name.'Number';

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Use Input as Search Conditions for the current Builder.
            """
            directive @searchBy on ARGUMENT_DEFINITION
        GRAPHQL;
    }

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode &$parentType,
    ): void {
        $this->getContainer()
            ->make(Manipulator::class, ['document' => $documentAST])
            ->update($this->directiveNode, $argDefinition);
    }

    /**
     * @inheritDoc
     * @return EloquentBuilder<Model>|QueryBuilder
     */
    public function handleBuilder($builder, $value): EloquentBuilder|QueryBuilder {
        return $this->handleAnyBuilder($builder, $value);
    }
}
