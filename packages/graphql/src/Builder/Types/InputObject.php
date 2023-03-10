<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Types;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionFieldAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

use function count;
use function trim;

abstract class InputObject implements TypeDefinition {
    public function __construct() {
        // empty
    }

    abstract protected function getScope(): string;

    abstract protected function getDescription(
        Manipulator $manipulator,
        InputSource|ObjectSource $source,
    ): string;

    /**
     * @inheritDoc
     */
    public function getTypeDefinitionNode(
        Manipulator $manipulator,
        string $name,
        ?TypeSource $source,
    ): ?TypeDefinitionNode {
        // Source?
        if (!($source instanceof InputSource) && !($source instanceof ObjectSource)) {
            return null;
        }

        // Type
        $description = $this->getDescription($manipulator, $source);
        $operators   = $this->getOperators($manipulator, $source);
        $definition  = Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            {$description}
            """
            input {$name} {
                """
                If you see this probably something wrong. Please contact to developer.
                """
                dummy: ID

                {$manipulator->getOperatorsFields($operators, $source)}
            }
            DEF,
        );

        // Add searchable fields
        $object = $source->getType();
        $fields = $object instanceof InputObjectType || $object instanceof ObjectType
            ? $object->getFields()
            : $object->fields;

        foreach ($fields as $field) {
            // Name should be unique (may conflict with Type's operators)
            $fieldName = $manipulator->getNodeName($field);

            if (isset($definition->fields[$fieldName])) {
                throw new TypeDefinitionFieldAlreadyDefined($fieldName);
            }

            // Field & Type
            $fieldSource = $source->getField($field);

            if (!$this->isFieldConvertable($manipulator, $fieldSource)) {
                continue;
            }

            // Add
            $fieldDefinition = $this->getFieldDefinition($manipulator, $fieldSource);

            if ($fieldDefinition) {
                $definition->fields[] = $fieldDefinition;
            }
        }

        // Remove dummy
        unset($definition->fields[0]);

        // Empty?
        if (count($definition->fields) === 0) {
            return null;
        }

        // Return
        return $definition;
    }

    /**
     * @return array<Operator>
     */
    protected function getOperators(
        Manipulator $manipulator,
        InputSource|ObjectSource $source,
    ): array {
        return [];
    }

    protected function isFieldConvertable(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource $field,
    ): bool {
        // Union?
        if ($manipulator->isUnion($field->getType())) {
            return false;
        }

        // Resolver?
        if ($manipulator->getNodeDirective($field->getField(), FieldResolver::class)) {
            return false;
        }

        // Ok
        return true;
    }

    protected function getFieldDefinition(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource $field,
    ): InputValueDefinitionNode|null {
        [$operator, $type] = $this->getFieldOperator($manipulator, $field) ?? [null, null];

        if ($operator === null || !$operator->isBuilderSupported($manipulator->getBuilderInfo()->getBuilder())) {
            return null;
        }

        if ($type === null) {
            $type = $manipulator->getTypeSource($field->getTypeDefinition());
        }

        $fieldName       = $manipulator->getNodeName($field->getField());
        $fieldDesc       = $this->getFieldDescription($manipulator, $field);
        $fieldDefinition = $manipulator->getOperatorField($operator, $type, $fieldName, $fieldDesc);

        return Parser::inputValueDefinition($fieldDefinition);
    }

    /**
     * @return array{Operator, ?TypeSource}|null
     */
    abstract protected function getFieldOperator(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource $field,
    ): ?array;

    /**
     * @template T of Operator
     *
     * @param class-string<T> $directive
     *
     * @return ?T
     */
    protected function getFieldDirectiveOperator(
        string $directive,
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource $field,
    ): ?Operator {
        // Directive?
        $operator = null;
        $builder  = $manipulator->getBuilderInfo()->getBuilder();
        $nodes    = [$field->getField(), $field->getTypeDefinition()];

        foreach ($nodes as $node) {
            $operator = $manipulator->getNodeDirective(
                $node,
                $directive,
                static function (Operator $operator) use ($builder): bool {
                    return $operator->isBuilderSupported($builder);
                },
            );

            if ($operator) {
                break;
            }
        }

        // Return
        return $operator;
    }

    protected function getFieldDescription(
        Manipulator $manipulator,
        ObjectFieldSource|InputFieldSource $field,
    ): string|null {
        $description = null;

        if ($field instanceof InputFieldSource) {
            $description = $field->getField()->description;
        }

        if ($description instanceof StringValueNode) {
            $description = $description->value;
        }

        if ($description) {
            $description = trim($description) ?: null;
        }

        return $description;
    }
}
