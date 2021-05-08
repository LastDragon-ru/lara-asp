<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\PackageTranslator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchBuilder;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

class Directive extends BaseDirective implements ArgManipulator, ArgBuilderDirective {
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
    public const ArgOperators  = 'operators';

    public function __construct(
        protected Container $container,
        protected PackageTranslator $translator,
    ) {
        // empty
    }

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Convert Input into Search Conditions.
            """
            directive @searchBy on INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode &$parentType,
    ): void {
        $this->container
            ->make(Manipulator::class, ['document' => $documentAST])
            ->update($this->directiveNode, $argDefinition);
    }

    /**
     * @inheritdoc
     */
    public function handleBuilder($builder, $value): EloquentBuilder|QueryBuilder {
        $operators = $this->directiveArgValue(self::ArgOperators);
        $operators = (new Collection($operators))
            ->map(function (string $operator): object {
                return $this->container->make($operator);
            })
            ->all();

        return (new SearchBuilder(
            $this->translator,
            $operators,
        ))->build($builder, $value);
    }
}
