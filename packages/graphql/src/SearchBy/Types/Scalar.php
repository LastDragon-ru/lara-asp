<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;

class Scalar implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getTypeName(Manipulator $manipulator, BuilderInfo $builder, ?TypeSource $type): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();
        $typeName      = $type?->getTypeName();
        $nullable      = $type?->isNullable() ? 'OrNull' : '';

        return "{$directiveName}{$builderName}Scalar{$typeName}{$nullable}";
    }

    /**
     * @inheritDoc
     */
    public function getTypeDefinitionNode(
        Manipulator $manipulator,
        string $name,
        ?TypeSource $type,
    ): ?TypeDefinitionNode {
        // Type?
        if (!($type instanceof ObjectFieldSource)) {
            return null;
        }

        // Operators
        $scope     = Directive::class;
        $operators = $manipulator->getTypeOperators($scope, $type);

        if (!$operators) {
            return null;
        }

        // Definition
        $content    = $manipulator->getOperatorsFields($operators, $type);
        $typeName   = $manipulator->getNodeTypeFullName($type->getType()).($type->isNullable() ? '' : '!');
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
