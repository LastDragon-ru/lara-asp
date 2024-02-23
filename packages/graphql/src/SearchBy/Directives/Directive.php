<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FailedToCreateSearchCondition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Root;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Scout\ScoutBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;
use Override;

use function str_starts_with;

class Directive extends HandlerDirective implements ArgManipulator, ArgBuilderDirective, ScoutBuilderDirective {
    final public const Name = 'SearchBy';

    #[Override]
    public static function definition(): string {
        $name = DirectiveLocator::directiveName(static::class);

        return <<<GRAPHQL
            """
            Use Input as Search Conditions for the current Builder.
            """
            directive @{$name} on ARGUMENT_DEFINITION
        GRAPHQL;
    }

    // <editor-fold desc="Manipulate">
    // =========================================================================
    #[Override]
    protected function isTypeName(string $name): bool {
        return str_starts_with($name, self::Name);
    }

    #[Override]
    protected function getArgDefinitionType(
        Manipulator $manipulator,
        DocumentAST $document,
        ObjectFieldArgumentSource|InterfaceFieldArgumentSource $argument,
        Context $context,
    ): ListTypeNode|NamedTypeNode|NonNullTypeNode {
        $context = $context->override([HandlerContextOperators::class => new HandlerContextOperators(new Operators())]);
        $type    = $this->getArgumentTypeDefinitionNode($manipulator, $document, $argument, $context, Root::class);

        if (!$type) {
            throw new FailedToCreateSearchCondition($argument);
        }

        return $type;
    }
    // </editor-fold>

    // <editor-fold desc="Handle">
    // =========================================================================
    #[Override]
    public function handle(object $builder, Field $field, ArgumentSet $conditions, Context $context): object {
        // Some relations (eg `HasManyThrough`) require a table name prefix to
        // avoid "SQLSTATE[23000]: Integrity constraint violation: 1052 Column
        // 'xxx' in where clause is ambiguous" error.
        if ($builder instanceof EloquentBuilder && $field->getPath() === []) {
            $field = $field->getChild($builder->getModel()->getTable());
        }

        // Return
        return parent::handle($builder, $field, $conditions, $context);
    }
    // </editor-fold>
}
