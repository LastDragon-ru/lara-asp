<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\InputFieldAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Property;

use function array_shift;
use function is_string;

class Condition implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getTypeName(BuilderInfo $builder, string $type = null, bool $nullable = null): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();

        return "{$directiveName}{$builderName}Condition{$type}";
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
            return null;
        }

        // Supported?
        $node        = $manipulator->getTypeDefinitionNode($type);
        $isSupported = $node instanceof InputObjectTypeDefinitionNode
            || $node instanceof InputObjectType;

        if (!$isSupported) {
            return null;
        }

        // Logical
        $logical    = $manipulator->getTypeOperators(Operators::Logical, false);
        $content    = $manipulator->getOperatorsFields($logical, $name);
        $definition = Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available conditions for `{$manipulator->getNodeTypeFullName($node)}` (only one property allowed at a time).
            """
            input {$name} {
                {$content}
            }
            DEF,
        );

        // Add searchable fields
        $fields = $node instanceof InputObjectType
            ? $node->getFields()
            : $node->fields;

        foreach ($fields as $field) {
            /** @var InputValueDefinitionNode|InputObjectField $field */

            // Name should be unique (may conflict with Logical names)
            $fieldName = $manipulator->getNodeName($field);

            if (isset($definition->fields[$fieldName])) {
                throw new InputFieldAlreadyDefined($fieldName);
            }

            // Determine type
            $fieldType     = $manipulator->getNodeTypeName($field);
            $fieldTypeNode = null;

            try {
                $fieldTypeNode = $manipulator->getTypeDefinitionNode($field);
            } catch (TypeDefinitionUnknown $exception) {
                if ($manipulator->hasTypeOperators($fieldType)) {
                    $fieldTypeNode = $manipulator->getScalarTypeDefinitionNode($fieldType);
                } else {
                    throw $exception;
                }
            }

            // Add
            $fieldDefinition = $this->getFieldDefinition($manipulator, $field, $fieldTypeNode);

            if ($fieldDefinition) {
                $definition->fields[] = $fieldDefinition;
            }
        }

        // Return
        return $definition;
    }

    protected function getFieldDefinition(
        Manipulator $manipulator,
        InputValueDefinitionNode|InputObjectField $field,
        Type|TypeDefinitionNode $fieldType,
    ): InputValueDefinitionNode|null {
        // Type or Operator
        $definition = match (true) {
            $fieldType instanceof ScalarTypeDefinitionNode,
                $fieldType instanceof ScalarType      => Scalar::class,
            $fieldType instanceof EnumTypeDefinitionNode,
                $fieldType instanceof EnumType        => Enumeration::class,
            $fieldType instanceof InputObjectTypeDefinitionNode,
                $fieldType instanceof InputObjectType => $this->getFieldOperator($manipulator, $field, $fieldType),
            default                                   => null,
        };

        if (!$definition) {
            throw new NotImplemented($manipulator->getNodeTypeFullName($fieldType));
        }

        // Create input
        $name     = $manipulator->getNodeName($field);
        $type     = $manipulator->getNodeName($fieldType);
        $operator = null;

        if (is_string($definition)) {
            $operator = $manipulator->getOperator(Property::class);
            $nullable = $manipulator->isNullable($field);
            $type     = $manipulator->getType($definition, $type, $nullable);
        } else {
            $operator = $definition;
        }

        return Parser::inputValueDefinition(
            $manipulator->getOperatorField($operator, $type, $name),
        );
    }

    protected function getFieldOperator(
        Manipulator $manipulator,
        InputValueDefinitionNode|InputObjectField $field,
        Type|TypeDefinitionNode $fieldType,
    ): Operator {
        // From directive
        $operator = null;
        $nodes    = [$field, $fieldType];

        do {
            $node     = array_shift($nodes);
            $operator = $node
                ? $manipulator->getNodeDirective($node, Operator::class)
                : null;
        } while ($node && $operator === null);

        // Default
        if (!$operator) {
            $operator = $manipulator->getOperator(Relation::class);
        }

        // Return
        return $operator;
    }
}
