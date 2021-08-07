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
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\AstManipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\OperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\RelationOperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ComplexOperatorInvalidTypeName;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\DefinitionImpossibleToCreateType;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\EnumNoOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FailedCreateSearchConditionForField;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FakeTypeDefinitionIsNotFake;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\FakeTypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ImpossibleCreateSearchCondition;
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
use function json_encode;
use function tap;

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
        $operators = $this->metadata->getUsage()->get($def->name->value);

        if (empty($operators)) {
            $type = null;
            $name = null;

            if ($def instanceof InputObjectTypeDefinitionNode) {
                $name = $this->getInputType($def);
                $type = Parser::typeReference($name);
            }

            if (!($type instanceof NamedTypeNode)) {
                throw new ImpossibleCreateSearchCondition($node->name->value);
            }

            // Update
            $operators  = $this->metadata->getUsage()->get($name);
            $node->type = $type;
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
        $name = $definition->name->value;

        $this->addTypeDefinition($definition);
        $this->metadata->addType($internal, $name);

        // Return
        return $name;
    }

    public function getInputType(InputObjectTypeDefinitionNode $node): string {
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
            return $this->getOperatorType($operator, $scalar, false);
        }, $operators));
        $type      = $this->addTypeDefinition(Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available conditions for input {$node->name->value} (only one property allowed at a time).
            """
            input {$name} {
                {$content}
            }
            DEF,
        ));

        // Add searchable fields
        $description = Parser::description('"""Property condition."""');

        /** @var InputValueDefinitionNode $field */
        foreach ($node->fields as $field) {
            // Name should be unique
            $fieldName = $field->name->value;

            if (isset($type->fields[$fieldName])) {
                throw new InputFieldAlreadyDefined($fieldName);
            }

            // Determine type
            $fieldType       = $this->getNodeTypeName($field);
            $fieldNullable   = !($field->type instanceof NonNullTypeNode);
            $fieldTypeNode   = $this->getTypeDefinitionNode($field);
            $fieldDefinition = null;

            if ($fieldTypeNode instanceof Type) {
                if ($fieldTypeNode->astNode) {
                    $fieldTypeNode = $fieldTypeNode->astNode;
                } elseif ($fieldTypeNode instanceof EnumType) {
                    $fieldTypeNode = $this->getFakeEnumTypeNode($fieldType);
                } elseif ($fieldTypeNode instanceof ScalarType) {
                    $fieldTypeNode = $this->getScalarTypeNode($fieldType);
                } else {
                    $fieldTypeNode = null;
                }
            } elseif ($this->metadata->isScalar($fieldType)) {
                $fieldTypeNode = $this->getScalarTypeNode($fieldType);
            } else {
                // empty
            }

            // Create Type for Search
            if ($fieldTypeNode instanceof ScalarTypeDefinitionNode) {
                $fieldDefinition = $this->getScalarType($fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof InputObjectTypeDefinitionNode) {
                $fieldDefinition = $this->getComplexType($field, $fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof EnumTypeDefinitionNode) {
                $fieldDefinition = $this->getEnumType($fieldTypeNode, $fieldNullable);
            } else {
                // empty
            }

            // Create new Field
            if ($fieldDefinition) {
                // TODO [SearchBy] We probably not need all directives from the
                //      original Input type, but cloning is the easiest way...
                $clone = $field->cloneDeep();

                if ($clone instanceof InputValueDefinitionNode) {
                    $clone->type        = Parser::typeReference($fieldDefinition);
                    $clone->description = $description;
                    $type->fields[]     = $clone;
                } else {
                    throw new FailedCreateSearchConditionForField($node->name->value, $fieldName);
                }
            } elseif ($fieldTypeNode) {
                throw new NotImplemented($fieldType);
            } else {
                // empty
            }
        }

        // End usage
        $this->metadata->getUsage()->end($usage);

        // Return
        return $name;
    }

    public function getEnumType(EnumTypeDefinitionNode $type, bool $nullable): string {
        // Exists?
        $name = $this->getEnumTypeName($type, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            $this->metadata->getUsage()->addType($name);

            return $name;
        }

        // Determine supported operators
        $enum      = $type->name->value;
        $usage     = $this->metadata->getUsage()->start($name);
        $operators = $this->getEnumOperators($enum, $nullable);

        // Add type
        $content = implode("\n", array_map(function (Operator $operator) use ($type, $nullable): string {
            return $this->getOperatorType($operator, $type, $nullable);
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

    public function getScalarType(ScalarTypeDefinitionNode $type, bool $nullable): string {
        // Exists?
        $name = $this->getScalarTypeName($type, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            $this->metadata->getUsage()->addType($name);

            return $name;
        }

        // Determine supported operators
        $usage     = $this->metadata->getUsage()->start($name);
        $scalar    = $type->name->value;
        $operators = $this->getScalarOperators($scalar, $nullable);

        // Add type
        $mark    = $nullable ? '' : '!';
        $content = implode("\n", array_map(function (Operator $operator) use ($type, $nullable): string {
            return $this->getOperatorType($operator, $type, $nullable);
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
        ScalarTypeDefinitionNode|EnumTypeDefinitionNode $node,
        bool $nullable,
    ): string {
        return $operator->getDefinition($this, $node->name->value, $nullable);
    }

    protected function getComplexType(
        InputValueDefinitionNode $field,
        InputObjectTypeDefinitionNode $type,
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

        if ($name !== $definition->name->value) {
            throw new ComplexOperatorInvalidTypeName($operator::class, $name, $definition->name->value);
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

    protected function getConditionTypeName(InputObjectTypeDefinitionNode $node): string {
        return Directive::Name."Condition{$node->name->value}";
    }

    protected function getEnumTypeName(EnumTypeDefinitionNode $node, bool $nullable): string {
        return Directive::Name."Enum{$node->name->value}".($nullable ? 'OrNull' : '');
    }

    protected function getScalarTypeName(ScalarTypeDefinitionNode $node, bool $nullable): string {
        return Directive::Name."Scalar{$node->name->value}".($nullable ? 'OrNull' : '');
    }

    protected function getComplexTypeName(
        InputObjectTypeDefinitionNode $node,
        ComplexOperator $operator,
    ): string {
        $name     = $node->name->value;
        $operator = Str::studly($operator->getName());

        return Directive::Name."Complex{$operator}{$name}";
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @return array<class-string<Operator>>
     */
    protected function getEnumOperators(string $enum, bool $nullable): array {
        $operators = $this->metadata->getEnumOperators($enum, $nullable);

        if (empty($operators)) {
            throw new EnumNoOperators($enum);
        }

        return $operators;
    }

    /**
     * @return array<class-string<Operator>>
     */
    protected function getScalarOperators(string $scalar, bool $nullable): array {
        $operators = $this->metadata->getScalarOperators($scalar, $nullable);

        if (empty($operators)) {
            throw new ScalarNoOperators($scalar);
        }

        return $operators;
    }

    protected function getComplexOperator(
        InputValueDefinitionNode|InputObjectTypeDefinitionNode ...$nodes,
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

        if (count($fake->fields) !== 1 || $fake->fields[0]->name->value !== 'fake') {
            throw new FakeTypeDefinitionIsNotFake($name);
        }

        // Remove
        unset($this->document->types[$name]);
    }

    public function getScalarTypeNode(string $scalar): ScalarTypeDefinitionNode {
        // TODO [GraphQL] Is there any better way for this?
        return Parser::scalarTypeDefinition("scalar {$scalar}");
    }

    protected function getFakeEnumTypeNode(string $scalar): EnumTypeDefinitionNode {
        // TODO [GraphQL] Is there any better way for this?
        return Parser::enumTypeDefinition("enum {$scalar} { fake }");
    }
    // </editor-fold>
}
