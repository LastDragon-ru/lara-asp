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

    public static function getTypeName(Manipulator $manipulator, BuilderInfo $builder, ?TypeSource $source): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();
        $typeName      = $source?->getTypeName();
        $nullable      = $source?->isNullable() ? 'OrNull' : '';

        return "{$directiveName}{$builderName}Scalar{$typeName}{$nullable}";
    }

    /**
     * @inheritDoc
     */
    public function getTypeDefinitionNode(
        Manipulator $manipulator,
        string $name,
        ?TypeSource $source,
    ): ?TypeDefinitionNode {
        // Type?
        if (!($source instanceof ObjectFieldSource)) {
            return null;
        }

        // Operators
        $scope     = Directive::class;
        $operators = $manipulator->getTypeOperators($scope, $source->getTypeName(), (bool) $source->isNullable());

        if (!$operators) {
            return null;
        }

        // Definition
        $content    = $manipulator->getOperatorsFields($operators, $source);
        $typeName   = $source->getTypeName().($source->isNullable() ? '' : '!');
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
