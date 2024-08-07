<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextBuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition\Root;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Scalar;
use Override;

class RelationshipType implements TypeDefinition {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        $typeName      = $source->getTypeName();
        $builderName   = $context->get(HandlerContextBuilderInfo::class)?->value->getName() ?? 'Unknown';
        $directiveName = Directive::Name;

        return "{$directiveName}{$builderName}Relationship{$typeName}";
    }

    #[Override]
    public function getTypeDefinition(
        Manipulator $manipulator,
        TypeSource $source,
        Context $context,
        string $name,
    ): (TypeDefinitionNode&Node)|string|null {
        // Object?
        if (!$source->isObject()) {
            return null;
        }

        // Definition
        $int    = $manipulator->getTypeSource(Type::nonNull(Type::int()));
        $count  = $manipulator->getType(Scalar::class, $int, $context);
        $where  = $manipulator->getType(Root::class, $source, $context);
        $origin = $manipulator->getTypeFullName($source->getType());

        return Parser::inputObjectTypeDefinition(
            <<<GRAPHQL
            """
            Conditions for the relationship (`has()`/`doesntHave()`) for `{$origin}`.

            See also:
            * https://laravel.com/docs/eloquent-relationships#querying-relationship-existence
            * https://laravel.com/docs/eloquent-relationships#querying-relationship-absence
            """
            input {$name} {
                """
                Additional conditions.
                """
                where: {$where}

                """
                Count conditions.
                """
                count: {$count}

                """
                Alias for `count: {greaterThanOrEqual: 1}`. Will be ignored if `count` used.
                """
                exists: Boolean

                """
                Alias for `count: {lessThan: 1}`. Will be ignored if `count` used.
                """
                notExists: Boolean! = false
            }
            GRAPHQL,
        );
    }
}
