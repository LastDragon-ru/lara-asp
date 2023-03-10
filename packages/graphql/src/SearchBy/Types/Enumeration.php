<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

class Enumeration implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getTypeName(Manipulator $manipulator, BuilderInfo $builder, ?TypeSource $source): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();
        $typeName      = $source?->getTypeName();
        $nullable      = $source?->isNullable() ? 'OrNull' : '';

        return "{$directiveName}{$builderName}Enum{$typeName}{$nullable}";
    }

    /**
     * @inheritDoc
     */
    public function getTypeDefinitionNode(
        Manipulator $manipulator,
        string $name,
        ?TypeSource $source,
    ): ?TypeDefinitionNode {
        // Source?
        if (!$source) {
            return null;
        }

        // Operators
        $scope     = Directive::class;
        $operators = $manipulator->hasTypeOperators($scope, $source->getTypeName())
            ? $manipulator->getTypeOperators($scope, $source->getTypeName(), (bool) $source->isNullable())
            : $manipulator->getTypeOperators($scope, Operators::Enum, (bool) $source->isNullable());

        if (!$operators) {
            return null;
        }

        // Definition
        $content    = $manipulator->getOperatorsFields($operators, $source);
        $typeName   = $manipulator->getNodeTypeFullName($source->getType());
        $definition = Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available operators for `{$typeName}` (only one operator allowed at a time).
            """
            input {$name} {
                {$content}
            }
            DEF,
        );

        // Return
        return $definition;
    }
}
