<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\GreaterThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\IsNotNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\IsNull;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;

use function array_merge;
use function implode;
use function is_array;
use function is_null;
use function sprintf;
use function tap;

class Manipulator {
    /**
     * @param array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator>>> $scalars
     */
    public function __construct(
        protected Container $container,
        protected DocumentAST $document,
        protected string $name,
        protected array $scalars,
    ) {
        // empty
    }

    public function getConditionsType(InputValueDefinitionNode $node): ListTypeNode {
        $type = null;

        if ((!$node->type instanceof ListTypeNode)) {
            $def = $this->getTypeDefinitionNode($node);

            if ($def instanceof InputObjectTypeDefinitionNode) {
                $name = $this->getInputType($def);
                $type = Parser::typeReference("[{$name}!]");
            }
        } else {
            $type = $node->type;
        }

        if ((!$type instanceof ListTypeNode)) {
            throw new SearchByException(sprintf(
                'Impossible to create Search Condition for `%s`.',
                $node->name->value,
            ));
        }

        return $type;
    }

    protected function getInputType(InputObjectTypeDefinitionNode $node): string {
        // Exists?
        $name = $this->getConditionTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add type
        /** @var \GraphQL\Language\AST\InputObjectTypeDefinitionNode $type */
        $type = Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available conditions.
            """
            input {$name} {
                and: [{$name}!]
                or: [{$name}!]
                not: [{$name}!]
            }
            DEF,
        );

        $this->document->setTypeDefinition($type);

        // Add searchable fields
        /** @var \GraphQL\Language\AST\InputValueDefinitionNode $field */
        foreach ($node->fields as $field) {
            // Create Type for Search
            $fieldType       = ASTHelper::getUnderlyingTypeName($field);
            $fieldNullable   = ($field->type instanceof NonNullTypeNode);
            $fieldTypeNode   = $this->getTypeDefinitionNode($field);
            $fieldDefinition = null;

            if (is_null($fieldTypeNode) && $this->isScalar($fieldType)) {
                // TODO [SearchBy] Is there any better way for this?
                $fieldTypeNode = $this->getScalarTypeNode($fieldType);
            }

            if ($fieldTypeNode instanceof InputObjectTypeDefinitionNode) {
                $fieldDefinition = $this->getRelationType($fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof ScalarTypeDefinitionNode) {
                $fieldDefinition = $this->getScalarType($fieldTypeNode, $fieldNullable);
            } else {
                // empty
            }

            // Create new Field
            if ($fieldDefinition) {
                // TODO [SearchBy] We probably not need all directives from the
                //      original Input type, but cloning is the easiest way...
                $type->fields[] = tap(
                    $field->cloneDeep(),
                    static function (InputValueDefinitionNode $field) use ($fieldDefinition): void {
                        $field->type = Parser::typeReference($fieldDefinition);
                    },
                );
            } else {
                throw new SearchByException(sprintf(
                    'Hmm... Seems `%s` not yet supported :( Please contact to developer.',
                    $fieldType,
                ));
            }
        }

        // Return
        return $name;
    }

    protected function getScalarType(ScalarTypeDefinitionNode $node, bool $nullable): string {
        // Exists?
        $name = $this->getScalarTypeName($node, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Determine supported operators
        $type      = $node->name->value;
        $operators = $type;

        do {
            $operators = $this->scalars[$operators] ?? [];
        } while (!is_array($operators));

        if (empty($operators)) {
            throw new SearchByException(sprintf(
                'Generated scalar type is empty. Please check definition for `%s` scalar.',
                $type,
            ));
        }

        // Add null for nullable
        if ($nullable) {
            $operators[] = IsNull::class;
            $operators[] = IsNotNull::class;
        }

        // Generate
        $body = [];

        foreach ($operators as $operator) {
            $operator     = $this->getOperator($operator);
            $operatorType = $this->getOperatorType($node, $type, $nullable, $operator);

            $body[] = $operator->getDefinition($operatorType, $nullable);
        }

        // Add type
        $content = implode("\n", $body);

        $this->document->setTypeDefinition(Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available operators for {$type} (only one operator allowed at a time).
            """
            input {$name} {
                {$content}
            }
            DEF,
        ));

        // Return
        return $name;
    }

    protected function getRelationType(InputObjectTypeDefinitionNode $node, bool $nullable): string {
        // Exists?
        $name = $this->getRelationTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add dummy type to avoid infinite loop
        $this->addDummyType($this->document, $name);

        // Add type
        $this->document->setTypeDefinition(Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Where Has condition.
            """
            input {$name} {
                has: Boolean = true
                where: [{$this->getInputType($node)}!]
                count: {$this->getScalarType($this->getScalarTypeNode('Int'), false)} = {
                    {$this->getOperator(GreaterThanOrEqual::class)->getName()}: 1
                }
            }
            DEF,
        ));

        // Return
        return $name;
    }

    protected function getOperatorType(
        ScalarTypeDefinitionNode $node,
        string $type,
        bool $nullable,
        Operator $operator,
    ): string {
        $name  = $type;
        $types = [];

        if ($operator instanceof OperatorHasTypes) {
            $name  = $this->getOperatorTypeName($operator);
            $types = array_merge($operator->getTypeDefinitions($name));
        }

        if ($operator instanceof OperatorHasTypesForScalar) {
            $name  = $this->getOperatorTypeName($operator, $this->getScalarTypeName($node, false));
            $types = array_merge($operator->getTypeDefinitionsForScalar($name, $type));
        }

        if ($operator instanceof OperatorHasTypesForScalarNullable) {
            $name  = $this->getOperatorTypeName($operator, $this->getScalarTypeName($node, $nullable));
            $types = array_merge($operator->getTypeDefinitionsForScalar($name, $type, $nullable));
        }

        foreach ($types as $type) {
            $this->document->setTypeDefinition($type);
        }

        return $name;
    }

    protected function getTypeName(string $name): string {
        return "{$this->name}Type{$name}";
    }

    protected function getConditionTypeName(InputObjectTypeDefinitionNode $node): string {
        return "{$this->name}Condition{$node->name->value}";
    }

    protected function getScalarTypeName(ScalarTypeDefinitionNode $node, bool $nullable): string {
        return "{$this->name}Scalar{$node->name->value}".($nullable ? 'Nullable' : '');
    }

    protected function getRelationTypeName(InputObjectTypeDefinitionNode $node): string {
        return "{$this->name}Relation{$node->name->value}";
    }

    protected function getOperatorTypeName(Operator $operator, string $base = null): string {
        return ($base ?: "{$this->name}Operator").Str::studly($operator->getName());
    }

    protected function getScalarTypeNode(string $scalar): ScalarTypeDefinitionNode {
        return Parser::scalarTypeDefinition("scalar {$scalar}");
    }

    protected function isScalar(string $type): bool {
        return isset($this->scalars[$type]);
    }

    /**
     * @param class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator> $class
     */
    protected function getOperator(string $class): Operator {
        return $this->container->make($class);
    }

    // <editor-fold desc="AST Helpers">
    // =========================================================================
//    protected function getNodeName(NamedTypeNode $node): string {
//        return $node->
//    }

    protected function isTypeDefinitionExists(string $name): bool {
        return (bool) $this->getTypeDefinitionNode($name);
    }

    protected function getTypeDefinitionNode(Node|string $node): ?TypeDefinitionNode {
        $type       = $node instanceof Node
            ? ASTHelper::getUnderlyingTypeName($node)
            : $node;
        $definition = $this->document->types[$type] ?? null;

        return $definition;
    }

    protected function addDummyType(DocumentAST $document, string $name): void {
        $document->setTypeDefinition(Parser::inputObjectTypeDefinition(
        /** @lang GraphQL */
            <<<DEF
            """
            This is a dummy type that used internally. If you see it, this is
            probably a bug, please contact to developer.
            """
            input {$name} {
                dummy: Boolean!
            }
            DEF,
        ));
    }
    //</editor-fold>
}
