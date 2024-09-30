<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextBuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use Override;

use function array_merge;

class Enumeration implements TypeDefinition {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        $directiveName = Directive::Name;
        $builderName   = $context->get(HandlerContextBuilderInfo::class)?->value->getName() ?? 'Unknown';
        $typeName      = $source->getTypeName();
        $nullable      = $source->isNullable() ? 'OrNull' : '';

        return "{$directiveName}{$builderName}Enum{$typeName}{$nullable}";
    }

    #[Override]
    public function getTypeDefinition(
        Manipulator $manipulator,
        TypeSource $source,
        Context $context,
        string $name,
    ): (TypeDefinitionNode&Node)|string|null {
        // Enum?
        if (!$source->isEnum()) {
            return null;
        }

        // Operators?
        $provider = $context->get(HandlerContextOperators::class)?->value;

        if ($provider === null) {
            return null;
        }

        // Operators
        $type      = $manipulator->getTypeSource($source->getType());
        $operators = $provider->getOperators($manipulator, $type->getTypeName(), $type, $context)
            ?: $provider->getOperators($manipulator, Operators::Enum, $type, $context);

        if ($operators === []) {
            return null;
        }

        // Nullable?
        if ($type->isNullable()) {
            $operators = array_merge(
                $operators,
                $provider->getOperators($manipulator, Operators::Null, $type, $context),
            );
        }

        // Definition
        $content    = $manipulator->getOperatorsFields($operators, $type, $context);
        $definition = Parser::inputObjectTypeDefinition(
            <<<GRAPHQL
            """
            Available operators for `{$type}` (only one operator allowed at a time).
            """
            input {$name} {
                {$content}
            }
            GRAPHQL,
        );

        // Return
        return $definition;
    }
}
