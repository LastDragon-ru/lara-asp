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

    public function getConditionType(InputValueDefinitionNode $node): ListTypeNode {
        $type = null;

        if ((!$node->type instanceof ListTypeNode)) {
            $def = $this->getTypeDefinitionNode($node);

            if ($def instanceof InputObjectTypeDefinitionNode) {
                $name = $this->getQueryType($def);
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

    protected function getQueryType(InputObjectTypeDefinitionNode $node): string {
        // Exists?
        $name = $this->getQueryTypeName($node);

        if (isset($this->document->types[$name])) {
            return $name;
        }

        // Add dummy type to avoid infinite loop
        $this->addDummyType($this->document, $name);

        // Create
        $body = [];

        foreach ($node->fields as $field) {
            /** @var \GraphQL\Language\AST\InputValueDefinitionNode $field */

            $type       = ASTHelper::getUnderlyingTypeName($field);
            $nullable   = ($field->type instanceof NonNullTypeNode);
            $typeNode   = $this->getTypeDefinitionNode($field);
            $definition = null;

            if (is_null($typeNode) && $this->isScalar($type)) {
                // TODO Is there any better way for this?
                $typeNode = $this->getScalarTypeNode($type);
            }

            if ($typeNode instanceof InputObjectTypeDefinitionNode) {
                $definition = $this->getRelationType($typeNode, $nullable);
            } elseif ($typeNode instanceof ScalarTypeDefinitionNode) {
                $definition = $this->getScalarType($typeNode, $nullable);
            } else {
                // empty
            }

            if ($definition) {
                $body[] = "{$field->name->value}: {$definition}\n";
            } else {
                throw new SearchByException(sprintf(
                    'Hmm... Seems `%s` not yet supported :( Please contact to developer.',
                    $type,
                ));
            }
        }

        // Add type
        $content = implode("\n", $body);

        $this->document->setTypeDefinition(Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available conditions.
            """
            input {$name} {
                and: [{$name}!]
                or: [{$name}!]
                {$content}
            }
            DEF,
        ));

        // Return
        return $name;
    }

    protected function getScalarType(ScalarTypeDefinitionNode $node, bool $nullable): string {
        // Exists?
        $name = $this->getScalarTypeName($node, $nullable);

        if (isset($this->document->types[$name])) {
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

        if (isset($this->document->types[$name])) {
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
                where: [{$this->getQueryType($node)}!]
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

    protected function getQueryTypeName(InputObjectTypeDefinitionNode $node): string {
        return "{$this->name}Query{$node->name->value}";
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
    protected function getTypeDefinitionNode(Node $node): ?TypeDefinitionNode {
        $type       = ASTHelper::getUnderlyingTypeName($node);
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
