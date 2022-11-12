<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ScalarType;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator as BuilderManipulator;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ComplexOperatorInvalidTypeName;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FailedToCreateSearchCondition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\InputFieldAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Enumeration;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Scalar;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_shift;
use function is_string;
use function str_starts_with;

class Manipulator extends BuilderManipulator {
    public function __construct(
        DirectiveLocator $directives,
        DocumentAST $document,
        TypeRegistry $types,
        Container $container,
        BuilderInfo $builderInfo,
        Operators $operators,
    ) {
        parent::__construct($directives, $document, $types, $container, $builderInfo, $operators);
    }

    // <editor-fold desc="Update">
    // =========================================================================
    public function update(DirectiveNode $directive, InputValueDefinitionNode $node): void {
        $def  = $this->getTypeDefinitionNode($node);
        $type = null;

        if ($def instanceof InputObjectTypeDefinitionNode || $def instanceof InputObjectType) {
            $name = $this->getNodeTypeName($def);

            if (!str_starts_with($name, Directive::Name)) {
                $name = $this->getInputType($def);
                $type = Parser::typeReference($name);
            } else {
                $type = $node->type;
            }
        }

        if (!($type instanceof NamedTypeNode)) {
            throw new FailedToCreateSearchCondition($this->getNodeName($node));
        }

        // Update
        $node->type = $type;
    }
    // </editor-fold>

    // <editor-fold desc="Types">
    // =========================================================================
    public function getInputType(InputObjectTypeDefinitionNode|InputObjectType $node): string {
        // Exists?
        $name = $this->getConditionTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add type
        $logical = $this->getTypeOperators(Operators::Logical, false);
        $scalar  = $this->getScalarTypeNode($name);
        $content = $this->getOperatorsFields($logical, $scalar);
        $type    = $this->addTypeDefinition(
            Parser::inputObjectTypeDefinition(
                <<<DEF
                """
                Available conditions for `{$this->getNodeTypeFullName($node)}` (only one property allowed at a time).
                """
                input {$name} {
                    {$content}
                }
                DEF,
            ),
        );

        // Add searchable fields
        $operators = $this->getOperators();
        $property  = $this->getContainer()->make(Property::class);
        $fields    = $node instanceof InputObjectType
            ? $node->getFields()
            : $node->fields;

        foreach ($fields as $field) {
            /** @var InputValueDefinitionNode|InputObjectField $field */

            // Name should be unique
            $fieldName = $this->getNodeName($field);

            if (isset($type->fields[$fieldName])) {
                throw new InputFieldAlreadyDefined($fieldName);
            }

            // Determine type
            $fieldType       = $this->getNodeTypeName($field);
            $fieldNullable   = $field instanceof InputValueDefinitionNode
                ? !($field->type instanceof NonNullTypeNode)
                : !($field->getType() instanceof NonNull);
            $fieldTypeNode   = null;
            $fieldDefinition = null;

            try {
                $fieldTypeNode = $this->getTypeDefinitionNode($field);
            } catch (TypeDefinitionUnknown $exception) {
                if ($operators->hasOperators($fieldType)) {
                    $fieldTypeNode = $this->getScalarTypeNode($fieldType);
                } else {
                    throw $exception;
                }
            }

            // Create Type for Search
            if ($fieldTypeNode instanceof ScalarTypeDefinitionNode) {
                $fieldDefinition = $this->getScalarType($fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof ScalarType) {
                $fieldDefinition = $this->getScalarType($fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof InputObjectTypeDefinitionNode) {
                $fieldDefinition = $this->getComplexType($field, $fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof InputObjectType) {
                $fieldDefinition = $this->getComplexType($field, $fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof EnumTypeDefinitionNode) {
                $fieldDefinition = $this->getEnumType($fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof EnumType) {
                $fieldDefinition = $this->getEnumType($fieldTypeNode, $fieldNullable);
            } else {
                // empty
            }

            // Create new Field
            if (is_string($fieldDefinition)) {
                $fieldDefinition = Parser::inputValueDefinition(
                    $this->getOperatorField(
                        $property,
                        $this->getTypeDefinitionNode($fieldDefinition),
                        $this->getNodeName($field),
                    ),
                );
            }

            if ($fieldDefinition instanceof InputValueDefinitionNode) {
                $type->fields[] = $fieldDefinition;
            } else {
                throw new NotImplemented($fieldType);
            }
        }

        // Return
        return $name;
    }

    public function getEnumType(EnumTypeDefinitionNode|EnumType $type, bool $nullable): string {
        return $this->getType(Enumeration::class, $this->getNodeName($type), $nullable);
    }

    public function getScalarType(ScalarTypeDefinitionNode|ScalarType $type, bool $nullable): string {
        return $this->getType(Scalar::class, $this->getNodeName($type), $nullable);
    }

    protected function getComplexType(
        InputValueDefinitionNode|InputObjectField $field,
        InputObjectTypeDefinitionNode|InputObjectType $type,
        bool $nullable,
    ): InputValueDefinitionNode {
        // Prepare
        $operator = $this->getComplexOperator($field, $type);
        $typeName = $this->getNodeName($type);
        $name     = $operator->getFieldType($this, $typeName);
        $name     = $name === $typeName
            ? $this->getComplexTypeName($type, $operator)
            : $typeName;

        // Definition
        if (!$this->isTypeDefinitionExists($name)) {
            // Fake
            $this->addFakeTypeDefinition($name);

            // Create
            $definition = $operator->getDefinition($this, $field, $type, $name, $nullable);

            if ($name !== $this->getNodeName($definition)) {
                throw new ComplexOperatorInvalidTypeName($operator::class, $name, $this->getNodeName($definition));
            }

            $this->removeFakeTypeDefinition($name);
            $this->addTypeDefinition($definition);
        }

        // Return
        return Parser::inputValueDefinition(
            $this->getOperatorField(
                $operator,
                $this->getTypeDefinitionNode($name),
                $this->getNodeName($field),
            ),
        );
    }
    // </editor-fold>

    // <editor-fold desc="Names">
    // =========================================================================
    protected function getConditionTypeName(InputObjectTypeDefinitionNode|InputObjectType $node): string {
        $directiveName = Directive::Name;
        $builderName   = $this->getBuilderInfo()->getName();
        $nodeName      = $this->getNodeName($node);

        return "{$directiveName}{$builderName}Condition{$nodeName}";
    }

    protected function getComplexTypeName(
        InputObjectTypeDefinitionNode|InputObjectType $node,
        ComplexOperator $operator,
    ): string {
        $directiveName = Directive::Name;
        $builderName   = $this->getBuilderInfo()->getName();
        $nodeName      = $this->getNodeName($node);
        $operatorName  = Str::studly($operator::getName());

        return "{$directiveName}{$builderName}Complex{$operatorName}{$nodeName}";
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getComplexOperator(
        InputValueDefinitionNode|InputObjectTypeDefinitionNode|InputObjectField|InputObjectType ...$nodes,
    ): ComplexOperator {
        // Class
        $operator = null;

        do {
            $node     = array_shift($nodes);
            $operator = $node
                ? $this->getNodeDirective($node, ComplexOperator::class)
                : null;
        } while ($node && $operator === null);

        // Default
        if (!$operator) {
            $operator = $this->getContainer()->make(Relation::class);
        }

        // Return
        return $operator;
    }
    // </editor-fold>

    // <editor-fold desc="AST Helpers">
    // =========================================================================
    public function getScalarTypeNode(string $scalar): ScalarTypeDefinitionNode {
        // TODO [GraphQL] Is there any better way for this?
        return Parser::scalarTypeDefinition("scalar {$scalar}");
    }
    // </editor-fold>
}
