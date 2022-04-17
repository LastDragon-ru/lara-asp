<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

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
use LastDragon_ru\LaraASP\GraphQL\AstManipulator;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\OperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\RelationOperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ComplexOperatorInvalidTypeName;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\DefinitionImpossibleToCreateType;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\EnumNoOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FailedToCreateSearchCondition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FailedToCreateSearchConditionForField;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FakeTypeDefinitionIsNotFake;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FakeTypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\InputFieldAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\NotImplemented;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ScalarNoOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Flag;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Range;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_map;
use function array_shift;
use function count;
use function implode;
use function is_null;
use function is_object;
use function json_encode;

class Manipulator extends AstManipulator implements TypeProvider {
    protected Metadata $metadata;

    public function __construct(
        DirectiveLocator $directives,
        DocumentAST $document,
        TypeRegistry $types,
        Repository $metadata,
        protected Container $container,
    ) {
        $this->metadata = $metadata->get($document);

        parent::__construct($directives, $document, $types);
    }

    // <editor-fold desc="Update">
    // =========================================================================
    public function update(DirectiveNode $directive, InputValueDefinitionNode $node): void {
        // Transform
        $def       = $this->getTypeDefinitionNode($node);
        $operators = $this->metadata->getUsage()->get($this->getNodeName($def));

        if (!$operators) {
            $type = null;
            $name = null;

            if ($def instanceof InputObjectTypeDefinitionNode || $def instanceof InputObjectType) {
                $name = $this->getInputType($def);
                $type = Parser::typeReference($name);
            }

            if (!($type instanceof NamedTypeNode)) {
                throw new FailedToCreateSearchCondition($this->getNodeName($node));
            }

            // Update
            $node->type = $type;
            $operators  = $name
                ? $this->metadata->getUsage()->get($name)
                : [];
        }

        // Update
        $this->updateDirective($directive, [
            Directive::ArgOperators => $operators,
        ]);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    protected function updateDirective(DirectiveNode $directive, array $arguments): void {
        foreach ($arguments as $name => $value) {
            $directive->arguments[] = Parser::constArgument($name.': '.json_encode($value));
        }
    }
    // </editor-fold>

    // <editor-fold desc="Types">
    // =========================================================================
    public function getType(string $type, string $scalar = null, bool $nullable = null): string {
        // Exists?
        $internal = $this->getTypeName($type, $scalar, $nullable);
        $name     = $this->metadata->getType($internal);

        if ($name && $this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Create new
        $definition = $this->metadata->getDefinition($type)->get($internal, $scalar, $nullable);

        if (!$definition) {
            throw new DefinitionImpossibleToCreateType($type, $scalar, $nullable);
        }

        // Save
        $name = $this->getNodeName($definition);

        $this->addTypeDefinition($definition);
        $this->metadata->addType($internal, $name);

        // Return
        return $name;
    }

    public function getInputType(InputObjectTypeDefinitionNode|InputObjectType $node): string {
        // Exists?
        $name = $this->getConditionTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            $this->metadata->getUsage()->addType($name);

            return $name;
        }

        // Add type
        $usage     = $this->metadata->getUsage()->start($name);
        $operators = $this->getScalarOperators(Directive::ScalarLogic, false);
        $scalar    = $this->getScalarTypeNode($name);
        $content   = implode("\n", array_map(function (Operator $operator) use ($scalar): string {
            return $this->getOperatorType($operator, $scalar);
        }, $operators));
        $type      = $this->addTypeDefinition(Parser::inputObjectTypeDefinition(
                <<<DEF
            """
            Available conditions for input {$this->getNodeName($node)} (only one property allowed at a time).
            """
            input {$name} {
                {$content}
            }
            DEF,
        ));

        // Add searchable fields
        $description = 'Property condition.';
        $fields      = $node instanceof InputObjectType
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
                if ($this->metadata->isScalar($fieldType)) {
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
            if ($fieldDefinition) {
                if (!$this->copyFieldToType($type, $field, $fieldDefinition, $description)) {
                    throw new FailedToCreateSearchConditionForField($this->getNodeName($node), $fieldName);
                }
            } else {
                throw new NotImplemented($fieldType);
            }
        }

        // End usage
        $this->metadata->getUsage()->end($usage);

        // Return
        return $name;
    }

    public function getEnumType(EnumTypeDefinitionNode|EnumType $type, bool $nullable): string {
        // Exists?
        $name = $this->getEnumTypeName($type, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            $this->metadata->getUsage()->addType($name);

            return $name;
        }

        // Determine supported operators
        $enum      = $this->getNodeName($type);
        $usage     = $this->metadata->getUsage()->start($name);
        $operators = $this->getEnumOperators($enum, $nullable);

        // Add type
        $content = implode("\n", array_map(function (Operator $operator) use ($type): string {
            return $this->getOperatorType($operator, $type);
        }, $operators));

        $this->addTypeDefinition(Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available operators for enum {$enum} (only one operator allowed at a time).
            """
            input {$name} {
                {$content}
            }
            DEF,
        ));

        // End usage
        $this->metadata->getUsage()->end($usage);

        // Return
        return $name;
    }

    public function getScalarType(ScalarTypeDefinitionNode|ScalarType $type, bool $nullable): string {
        // Exists?
        $name = $this->getScalarTypeName($type, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            $this->metadata->getUsage()->addType($name);

            return $name;
        }

        // Determine supported operators
        $usage     = $this->metadata->getUsage()->start($name);
        $scalar    = $this->getNodeName($type);
        $operators = $this->getScalarOperators($scalar, $nullable);

        // Add type
        $mark    = $nullable ? '' : '!';
        $content = implode("\n", array_map(function (Operator $operator) use ($type): string {
            return $this->getOperatorType($operator, $type);
        }, $operators));

        $this->addTypeDefinition(Parser::inputObjectTypeDefinition(
                <<<DEF
            """
            Available operators for scalar {$scalar}{$mark} (only one operator allowed at a time).
            """
            input {$name} {
                {$content}
            }
            DEF,
        ));

        // End usage
        $this->metadata->getUsage()->end($usage);

        // Return
        return $name;
    }

    protected function getOperatorType(
        Operator $operator,
        ScalarTypeDefinitionNode|ScalarType|EnumTypeDefinitionNode|EnumType $type,
    ): string {
        $type        = $this->getNodeName($type);
        $type        = $operator->getFieldType($this, $type);
        $field       = $operator::getName();
        $directive   = $operator::getDirectiveName();
        $description = $operator->getFieldDescription();

        return <<<DEF
            """
            {$description}
            """
            {$field}: {$type}
            {$directive}
        DEF;
    }

    protected function getComplexType(
        InputValueDefinitionNode|InputObjectField $field,
        InputObjectTypeDefinitionNode|InputObjectType $type,
        bool $nullable,
    ): string {
        // Exists?
        $operator = $this->getComplexOperator($field, $type);
        $name     = $this->getComplexTypeName($type, $operator);

        if ($this->isTypeDefinitionExists($name)) {
            $this->metadata->getUsage()->addType($name);

            return $name;
        }

        // Fake
        $this->addFakeTypeDefinition($name);

        // Create
        $usage                = $this->metadata->getUsage()->start($name);
        $definition           = $operator->getDefinition($this, $field, $type, $name, $nullable);
        $definition->fields[] = Parser::inputValueDefinition(
            <<<DEF
            """
            Complex operator marker.
            """
            {$operator->getName()}: {$this->getType(Flag::Name)}! = yes
            DEF,
        );

        if ($name !== $this->getNodeName($definition)) {
            throw new ComplexOperatorInvalidTypeName($operator::class, $name, $this->getNodeName($definition));
        }

        $this->removeFakeTypeDefinition($name);
        $this->addTypeDefinition($definition);

        // End usage
        $this->metadata->getUsage()->end($usage);

        // Return
        return $name;
    }
    // </editor-fold>

    // <editor-fold desc="Defaults">
    // =========================================================================
    protected function addDefaultTypeDefinitions(): void {
        $this->metadata->addDefinition(Flag::Name, Flag::class);
        $this->metadata->addDefinition(Range::Name, Range::class);
    }
    // </editor-fold>

    // <editor-fold desc="Names">
    // =========================================================================
    protected function getTypeName(string $name, string $scalar = null, bool $nullable = null): string {
        return Directive::Name.'Type'.Str::studly($name).($scalar ?: '').($nullable ? 'OrNull' : '');
    }

    protected function getConditionTypeName(InputObjectTypeDefinitionNode|InputObjectType $node): string {
        return Directive::Name."Condition{$this->getNodeName($node)}";
    }

    protected function getEnumTypeName(EnumTypeDefinitionNode|EnumType $node, bool $nullable): string {
        return Directive::Name."Enum{$this->getNodeName($node)}".($nullable ? 'OrNull' : '');
    }

    protected function getScalarTypeName(ScalarTypeDefinitionNode|ScalarType $node, bool $nullable): string {
        return Directive::Name."Scalar{$this->getNodeName($node)}".($nullable ? 'OrNull' : '');
    }

    protected function getComplexTypeName(
        InputObjectTypeDefinitionNode|InputObjectType $node,
        ComplexOperator $operator,
    ): string {
        $name     = $this->getNodeName($node);
        $operator = Str::studly($operator->getName());

        return Directive::Name."Complex{$operator}{$name}";
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @return array<Operator>
     */
    protected function getEnumOperators(string $enum, bool $nullable): array {
        $operators = $this->metadata->getEnumOperators($enum, $nullable);

        if (!$operators) {
            throw new EnumNoOperators($enum);
        }

        return $operators;
    }

    /**
     * @return array<Operator>
     */
    protected function getScalarOperators(string $scalar, bool $nullable): array {
        $operators = $this->metadata->getScalarOperators($scalar, $nullable);

        if (!$operators) {
            throw new ScalarNoOperators($scalar);
        }

        return $operators;
    }

    protected function getComplexOperator(
        InputValueDefinitionNode|InputObjectTypeDefinitionNode|InputObjectField|InputObjectType ...$nodes,
    ): ComplexOperator {
        // Class
        $class = null;

        do {
            $node      = array_shift($nodes);
            $directive = $node
                ? $this->getNodeDirective($node, OperatorDirective::class)
                : null;

            if ($directive instanceof OperatorDirective) {
                $class = $directive->getClass();
            }
        } while ($node && is_null($class));

        // Default
        if (!$class) {
            $class = $this->container->make(RelationOperatorDirective::class)->getClass();
        }

        // Return
        $operator = $this->metadata->getComplexOperatorInstance($class);

        // Return
        return $operator;
    }
    // </editor-fold>

    // <editor-fold desc="AST Helpers">
    // =========================================================================
    protected function addFakeTypeDefinition(string $name): void {
        $this->addTypeDefinition(Parser::inputObjectTypeDefinition(
                <<<DEF
            """
            Fake type to prevent circular dependency infinite loop.
            """
            input {$name} {
                fake: Boolean! = true
            }
            DEF,
        ));
    }

    protected function removeFakeTypeDefinition(string $name): void {
        // Possible?
        $fake = $this->getTypeDefinitionNode($name);

        if (!($fake instanceof InputObjectTypeDefinitionNode)) {
            throw new FakeTypeDefinitionUnknown($name);
        }

        if (count($fake->fields) !== 1 || $this->getNodeName($fake->fields[0]) !== 'fake') {
            throw new FakeTypeDefinitionIsNotFake($name);
        }

        // Remove
        $this->removeTypeDefinition($name);
    }

    public function getScalarTypeNode(string $scalar): ScalarTypeDefinitionNode {
        // TODO [GraphQL] Is there any better way for this?
        return Parser::scalarTypeDefinition("scalar {$scalar}");
    }
    // </editor-fold>
}
