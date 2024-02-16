<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Types;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\BlockString;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextImplicit;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Ignored;
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
use LastDragon_ru\LaraASP\GraphQL\Package;
use Nuwave\Lighthouse\Schema\Directives\RelationDirective;
use Nuwave\Lighthouse\Schema\Directives\RenameDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Override;

use function config;
use function count;
use function is_a;
use function is_string;
use function reset;
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
        $source = $manipulator->getTypeSource($source->getTypeDefinition());

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
        $fields = $this->getFields($manipulator, $source, $context);

        foreach ($fields as $field) {
            // Name should be unique (may conflict with Type's operators)
            $fieldName = $manipulator->getName($field);

            if (isset($definition->fields[$fieldName])) {
                throw new TypeDefinitionFieldAlreadyDefined($fieldName);
            }

            // Field & Type
            $fieldSource = $source->getField($field, $manipulator->getOriginType($field));

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
        $type      = $this->getTypeForOperators();
        $operators = $type
            ? $manipulator->getTypeOperators($type, $this->getScope(), $source, $context)
            : [];

        return $operators;
    }

    /**
     * @return iterable<array-key, FieldDefinition|FieldDefinitionNode|InputObjectField|InputValueDefinitionNode>
     */
    protected function getFields(
        Manipulator $manipulator,
        InterfaceSource|InputSource|ObjectSource $source,
        Context $context,
    ): iterable {
        $object = $source->getType();
        $fields = $object instanceof Type
            ? $object->getFields()
            : $object->fields;

        return $fields;
    }

    /**
     * Determines if the field can be converted or not.
     *
     * Explicit and Implicit (=placeholder) types processing a bit differently,
     * see {@see self::isFieldConvertableExplicit()} and {@see self::isFieldConvertableImplicit()}
     * accordingly. Field also can be marked by a special marker (class/interface)
     * as ignored, see {@see self::isFieldConvertableIgnored()} and
     * {@see self::getFieldMarkerIgnored()}.
     */
    protected function isFieldConvertable(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): bool {
        // Union?
        if ($field->isUnion()) {
            // todo(graphql): Would be nice to support Unions. Maybe just use
            //      fields with same name and type for all members?
            return false;
        }

        // Convertable?
        $convertable = $context->get(HandlerContextImplicit::class)?->value
            ? $this->isFieldConvertableImplicit($manipulator, $field, $context)
            : $this->isFieldConvertableExplicit($manipulator, $field, $context);

        if (!$convertable) {
            return false;
        }

        // Ignored?
        if ($this->isFieldConvertableIgnored($manipulator, $field, $context)) {
            return false;
        }

        // Ok
        return true;
    }

    protected function isFieldConvertableExplicit(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): bool {
        // Explicit type is an `input` and we are expecting this type was created
        // for the directive, so all fields are valid and available.
        return true;
    }

    protected function isFieldConvertableImplicit(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): bool {
        // Implicit type (=placeholder) is an `object`/`interface` and may contain
        // fields with arguments and/or `FieldResolver` directive - these fields
        // (most likely) cannot be used to modify the Builder.

        // Operator?
        $marker   = $this->getFieldMarkerOperator();
        $operator = $marker
            ? $manipulator->getDirective($field->getField(), $marker)
            : null;

        if ($operator) {
            return true;
        }

        // Resolver?
        $resolver = $manipulator->getDirective($field->getField(), FieldResolver::class);

        if ($resolver && !$this->isFieldConvertableResolver($manipulator, $field, $context, $resolver)) {
            return false;
        }

        // Object/Arguments allowed only if Resolver defined and convertable
        if (($field->hasArguments() || $field->isObject()) && !$resolver) {
            return false;
        }

        // Ok
        return true;
    }

    protected function isFieldConvertableResolver(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
        FieldResolver $directive,
    ): bool {
        return ($directive instanceof RelationDirective && $field->isObject())
            || (
                $directive instanceof RenameDirective
                && !$field->hasArguments()
                && !$field->isObject()
                && $this->isFieldDirectiveAllowed($manipulator, $field, $context, $directive)
            );
    }

    protected function isFieldConvertableIgnored(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): bool {
        // Marker?
        $marker = $this->getFieldMarkerIgnored();

        if (!$marker) {
            return false;
        }

        // Ignored?
        if ($field instanceof $marker || $manipulator->getDirective($field->getField(), $marker) !== null) {
            return true;
        }

        // Ignored type?
        $fieldType = $field->getTypeDefinition();

        if ($fieldType instanceof $marker || $manipulator->getDirective($fieldType, $marker) !== null) {
            return true;
        }

        // Nope
        return false;
    }

    /**
     * @return class-string<Operator>
     */
    abstract protected function getFieldMarkerOperator(): string;

    /**
     * @see self::isFieldConvertableIgnored()
     *
     * @return class-string<Ignored>|null
     */
    protected function getFieldMarkerIgnored(): ?string {
        return null;
    }

    protected function getFieldDefinition(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): ?InputValueDefinitionNode {
        // Operator?
        $operator = $this->getFieldOperator($manipulator, $field, $context);

        if ($operator === null || !$operator->isAvailable($manipulator, $field, $context)) {
            return null;
        }

        // Field
        $fieldName       = $manipulator->getName($field->getField());
        $fieldDesc       = $this->getFieldDescription($manipulator, $field, $context);
        $fieldDirectives = $this->getFieldDirectives($manipulator, $field, $context);
        $fieldDefinition = $manipulator->getOperatorField(
            $operator,
            $field,
            $context,
            $fieldName,
            $fieldDesc,
            $fieldDirectives,
        );

        return Parser::inputValueDefinition($fieldDefinition);
    }

    protected function getFieldOperator(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
    ): ?Operator {
        $operator = $this->getFieldOperatorDirective($manipulator, $field, $context, $this->getFieldMarkerOperator());

        if (!$operator) {
            $type = $this->getTypeForFieldOperator();

            if ($type) {
                $operators = $manipulator->getTypeOperators($type, $this->getScope(), $field, $context);
                $operator  = reset($operators) ?: null;
            }
        }

        return $operator;
    }

    /**
     * @template T of Operator
     *
     * @param class-string<T> $directive
     *
     * @return ?T
     */
    protected function getFieldOperatorDirective(
        Manipulator $manipulator,
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
        string $directive,
    ): ?Operator {
        // Directive?
        $operator = null;
        $nodes    = [$field->getField(), $field->getTypeDefinition()];

        foreach ($nodes as $node) {
            $operator = $manipulator->getDirective(
                $node,
                $directive,
                static function (Operator $operator) use ($manipulator, $field, $context): bool {
                    return $operator->isAvailable($manipulator, $field, $context);
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
            if ($this->isFieldDirectiveAllowed($manipulator, $field, $context, $directive)) {
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
        InputFieldSource|ObjectFieldSource|InterfaceFieldSource $field,
        Context $context,
        Directive $directive,
    ): bool {
        // Explicit type is an `input` and we are expecting this type was created
        // for the directive, so all field's directives are allowed.
        if (!$context->get(HandlerContextImplicit::class)?->value) {
            if ($directive instanceof Operator) {
                return is_a($directive, $this->getFieldMarkerOperator());
            }

            return true;
        }

        // Operator is always allowed
        if (is_a($directive, $this->getFieldMarkerOperator())) {
            return true;
        }

        // Allowed?
        $isAllowed = false;
        $allowed   = (array) config(Package::Name.'.builder.allowed_directives');

        foreach ($allowed as $class) {
            if (is_string($class) && $directive instanceof $class) {
                $isAllowed = true;
                break;
            }
        }

        return $isAllowed;
    }

    protected function getTypeForOperators(): ?string {
        return null;
    }

    protected function getTypeForFieldOperator(): ?string {
        return null;
    }
}
