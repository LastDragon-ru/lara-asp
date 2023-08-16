<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorClauseDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedToCreateSortClause;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Scout\ScoutBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function str_starts_with;

class Directive extends HandlerDirective implements ArgManipulator, ArgBuilderDirective, ScoutBuilderDirective {
    public const Name = 'SortBy';

    public static function definition(): string {
        return <<<'GRAPHQL'
            """
            Use Input as Sort Conditions for the current Builder.
            """
            directive @sortBy on ARGUMENT_DEFINITION
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
            SortByOperatorClauseDirective::class,
        );

        if (!$type) {
            throw new FailedToCreateSortClause($argument);
        }

        return $type;
    }
    // </editor-fold>
}
