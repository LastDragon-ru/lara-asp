<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contexts\AstManipulation;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use Override;

class Enumeration implements TypeDefinition {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        $directiveName = Directive::Name;
        $builderName   = $context->get(AstManipulation::class)?->builderInfo->getName() ?? 'Unknown';
        $typeName      = $source->getTypeName();
        $nullable      = $source->isNullable() ? 'OrNull' : '';

        return "{$directiveName}{$builderName}Enum{$typeName}{$nullable}";
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getTypeDefinition(
        Manipulator $manipulator,
        TypeSource $source,
        Context $context,
        string $name,
    ): TypeDefinitionNode|Type|null {
        // Operators
        $scope     = Directive::getScope();
        $extras    = $source->isNullable() ? [Operators::Null] : [];
        $operators = $manipulator->getTypeOperators($scope, $source->getTypeName(), ...$extras)
            ?: $manipulator->getTypeOperators($scope, Operators::Enum, ...$extras);

        if (!$operators) {
            return null;
        }

        // Definition
        $content    = $manipulator->getOperatorsFields($operators, $source, $context);
        $typeName   = $manipulator->getTypeFullName($source->getType());
        $definition = Parser::inputObjectTypeDefinition(
            <<<GRAPHQL
            """
            Available operators for `{$typeName}` (only one operator allowed at a time).
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
