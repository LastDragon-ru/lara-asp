<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Types;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\BlockString;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contexts\AstManipulation;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionFieldAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use Nuwave\Lighthouse\Schema\Directives\RenameDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Override;

use function count;
use function trim;

abstract class InputObject implements TypeDefinition {
    public function __construct() {
        // empty
    }

    /**
     * @return class-string<Scope>
     */
    abstract protected function getScope(): string;

    abstract protected function getDescription(
        Manipulator $manipulator,
        InputSource|ObjectSource|InterfaceSource $source,
        Context $context,
    ): string;

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
        // Source?
        if (
            !($source instanceof InterfaceSource || $source instanceof ObjectSource || $source instanceof InputSource)
        ) {
            return null;
        }

        // Type
        $description = $this->getDescription($manipulator, $source, $context);
        $description = BlockString::print($description);
        $operators   = $this->getOperators($manipulator, $source, $context);
        $definition  = Parser::inputObjectTypeDefinition(
            <<<GRAPHQL
            {$description}
            input {$name} {
                """
                If you see this probably something wrong. Please contact to developer.
                """
                dummy: ID

                {$manipulator->getOperatorsFields($operators, $source, $context)}
            }
            GRAPHQL,
        );

        // Add searchable fields
        $object = $source->getType();
        $fields = $object instanceof Type
            ? $object->getFields()
            : $object->fields;

        foreach ($fields as $field) {
            // Name should be unique (may conflict with Type's operators)
            $fieldName = $manipulator->getName($field);

            if (isset($definition->fields[$fieldName])) {
                throw new TypeDefinitionFieldAlreadyDefined($fieldName);
            }

            // Field & Type
            $fieldSource = $source->getField($field);

            if (!$this->isFieldConvertable($manipulator, $fieldSource, $context)) {
                continue;
            }

            // Add
            $fieldDefinition = $this->getFieldDefinition($manipulator, $fieldSource, $context);

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
     * @return list<Operator>
     */
    protected function getOperators(
        Manipulator $manipulator,
        InputSource|ObjectSource|InterfaceSource $source,
        Context $context,
    ): array {
        return [];
    }

    protected function isFieldConvertable(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): bool {
        // Args? (in general case we don't know how they should be converted)
        if ($field->hasArguments()) {
            return false;
        }

        // Union?
        if ($field->isUnion()) {
            return false;
        }

        // Resolver?
        $resolver = $manipulator->getDirective($field->getField(), FieldResolver::class);

        if ($resolver !== null && !$this->isFieldDirectiveAllowed($manipulator, $resolver, $context)) {
            return false;
        }

        // Ok
        return true;
    }

    protected function getFieldDefinition(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): ?InputValueDefinitionNode {
        // Builder?
        $builder = $context->get(AstManipulation::class)?->builderInfo->getBuilder();

        if (!$builder) {
            return null;
        }

        // Operator?
        [$operator, $type] = $this->getFieldOperator($manipulator, $field, $context) ?? [null, null];

        if ($operator === null || !$operator->isBuilderSupported($builder)) {
            return null;
        }

        if ($type === null) {
            $type = $manipulator->getTypeSource($field->getTypeDefinition());
        }

        // Field
        $fieldName       = $manipulator->getName($field->getField());
        $fieldDesc       = $this->getFieldDescription($manipulator, $field, $context);
        $fieldDirectives = $this->getFieldDirectives($manipulator, $field, $context);
        $fieldDefinition = $manipulator->getOperatorField(
            $operator,
            $type,
            $context,
            $fieldName,
            $fieldDesc,
            $fieldDirectives,
        );

        return Parser::inputValueDefinition($fieldDefinition);
    }

    /**
     * @return array{Operator, ?TypeSource}|null
     */
    abstract protected function getFieldOperator(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
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
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): ?Operator {
        // Builder?
        $builder = $context->get(AstManipulation::class)?->builderInfo->getBuilder();

        if (!$builder) {
            return null;
        }

        // Directive?
        $operator = null;
        $nodes    = [$field->getField(), $field->getTypeDefinition()];

        foreach ($nodes as $node) {
            $operator = $manipulator->getDirective(
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
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
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

    /**
     * @return list<DirectiveNode>
     */
    protected function getFieldDirectives(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): array {
        $directives = [];

        foreach ($manipulator->getDirectives($field->getField()) as $directive) {
            if ($this->isFieldDirectiveAllowed($manipulator, $directive, $context)) {
                $node = $manipulator->getDirectiveNode($directive);

                if ($node) {
                    $directives[] = $node;
                }
            }
        }

        return $directives;
    }

    protected function isFieldDirectiveAllowed(
        Manipulator $manipulator,
        Directive $directive,
        Context $context,
    ): bool {
        return $directive instanceof Operator
            || $directive instanceof RenameDirective;
    }
}
