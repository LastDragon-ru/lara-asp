<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

class Enumeration implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getTypeName(BuilderInfo $builder, ?string $type, ?bool $nullable): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();
        $isNull        = $nullable ? 'OrNull' : '';

        return "{$directiveName}{$builderName}Enum{$type}{$isNull}";
    }

    /**
     * @inheritDoc
     */
    public function getTypeDefinitionNode(
        Manipulator $manipulator,
        string $name,
        ?string $type,
        ?bool $nullable,
    ): ?TypeDefinitionNode {
        // Type?
        if (!$type) {
            return null;
        }

        // Operators
        $scope     = Directive::class;
        $nullable  = (bool) $nullable;
        $operators = $manipulator->hasTypeOperators($scope, $type)
            ? $manipulator->getTypeOperators($scope, $type, $nullable)
            : $manipulator->getTypeOperators($scope, Operators::Enum, $nullable);

        if (!$operators) {
            return null;
        }

        // Definition
        $content    = $manipulator->getOperatorsFields($operators, $type);
        $typeName   = $manipulator->getNodeTypeFullName($type);
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
