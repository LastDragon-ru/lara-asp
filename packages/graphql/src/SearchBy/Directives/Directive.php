<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorConditionDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FailedToCreateSearchCondition;
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

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public static function getScope(): string {
        return Scope::class;
    }
    // </editor-fold>

    // <editor-fold desc="Manipulate">
    // =========================================================================
    protected function isTypeName(string $name): bool {
        return str_starts_with($name, self::Name);
    }

    protected function getArgDefinitionType(
        Manipulator $manipulator,
        DocumentAST $document,
        ObjectFieldArgumentSource|InterfaceFieldArgumentSource $argument,
    ): ListTypeNode|NamedTypeNode|NonNullTypeNode {
        $type = $this->getArgumentTypeDefinitionNode(
            $manipulator,
            $document,
            $argument,
            SearchByOperatorConditionDirective::class,
        );

        if (!$type) {
            throw new FailedToCreateSearchCondition($argument);
        }

        return $type;
    }
    // </editor-fold>

    // <editor-fold desc="Handle">
    // =========================================================================
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
