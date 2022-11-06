<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\TypeDefinitionNoOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\TypeDefinitionNoTypeName;

class Scalar implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getName(BuilderInfo $builder, string $type = null, bool $nullable = null): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();
        $isNull        = $nullable ? 'OrNull' : '';

        return "{$directiveName}{$builderName}Scalar{$type}{$isNull}";
    }

    /**
     * @inheritDoc
     */
    public function getTypeDefinitionNode(
        Manipulator $manipulator,
        string $name,
        string $type = null,
        bool $nullable = null,
    ): ?TypeDefinitionNode {
        // Type?
        if (!$type) {
            throw new TypeDefinitionNoTypeName($this::class);
        }

        // Operators
        $nullable  = (bool) $nullable;
        $operators = $manipulator->getTypeOperators($type, $nullable);

        if (!$operators) {
            throw new TypeDefinitionNoOperators($this::class, $type);
        }

        // Add type
        $content    = $manipulator->getOperatorsFields($operators, $type);
        $typeName   = $manipulator->getNodeTypeFullName($type).($nullable ? '' : '!');
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
