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
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\GreaterThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\IsNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Not;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;

use function array_map;
use function array_merge;
use function implode;
use function is_a;
use function is_array;
use function is_null;
use function sprintf;
use function tap;

class Manipulator {
    public const TYPE_FLAG = 'Flag';

    /**
     * Maps internal (operators) names to fully qualified names.
     *
     * @var array<string,string>
     */
    protected array $map = [];
    /**
     * @var array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator>,\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator|null>
     */
    protected array $operators = [];

    /**
     * @param array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator>>> $scalars
     * @param array<string,string>                                                                 $aliases
     */
    public function __construct(
        protected Container $container,
        protected DocumentAST $document,
        protected string $name,
        protected array $scalars,
        protected array $aliases,
    ) {
        $this->addRootTypeDefinitions();
    }

    // <editor-fold desc="API">
    // =========================================================================
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
    // </editor-fold>

    // <editor-fold desc="Types">
    // =========================================================================
    protected function getInputType(InputObjectTypeDefinitionNode $node): string {
        // Exists?
        $name = $this->getConditionTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add type
        /** @var \GraphQL\Language\AST\InputObjectTypeDefinitionNode $type */
        $type = $this->addTypeDefinition($name, Parser::inputObjectTypeDefinition(
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
        ));

        // Add searchable fields
        /** @var \GraphQL\Language\AST\InputValueDefinitionNode $field */
        foreach ($node->fields as $field) {
            // Name should be unique
            $fieldName = $field->name->value;

            if (isset($type->fields[$fieldName])) {
                throw new SearchByException(sprintf(
                    'Property with name `%s` already defined.',
                    $fieldName,
                ));
            }

            // Create Type for Search
            $fieldType       = ASTHelper::getUnderlyingTypeName($field);
            $fieldNullable   = !($field->type instanceof NonNullTypeNode);
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

        // Add `null` for nullable
        if ($nullable) {
            $operators[] = IsNull::class;
        }

        // Add `not` for negationable
        if (Arr::first($operators, static fn(string $o) => is_a($o, OperatorNegationable::class, true))) {
            $operators[] = Not::class;
        }

        // Add type
        $scalar  = isset($this->aliases[$type])
            ? $this->getScalarTypeNode($this->aliases[$type])
            : $node;
        $content = implode("\n", array_map(function (string $operator) use ($scalar, $nullable): string {
            return $this->getScalarOperatorType($this->getOperator($operator), $scalar, $nullable);
        }, $operators));

        $this->addTypeDefinition($name, Parser::inputObjectTypeDefinition(
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

    protected function getScalarOperatorType(
        Operator $operator,
        ScalarTypeDefinitionNode $node,
        bool $nullable,
    ): string {
        // Add types for Scalars
        if ($operator instanceof OperatorHasTypesForScalar) {
            $this->addTypeDefinitions($operator, $operator->getTypeDefinitionsForScalar(
                $this->getScalarOperatorTypeName($operator, $node, false),
                $node->name->value,
            ));
        }

        if ($operator instanceof OperatorHasTypesForScalarNullable) {
            $this->addTypeDefinitions($operator, $operator->getTypeDefinitionsForScalar(
                $this->getScalarOperatorTypeName($operator, $node, $nullable),
                $node->name->value,
                $nullable,
            ));
        }

        // Named map
        $map  = array_merge(
            $this->map[$operator::class] ?? [],
            $this->map[$this::class] ?? [],
        );
        $type = $operator->getDefinition($map, $node->name->value, $nullable);

        // Return
        return $type;
    }

    protected function getRelationType(InputObjectTypeDefinitionNode $node, bool $nullable): string {
        // Exists?
        $name = $this->getRelationTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add type
        $type = $this->getInputType($node);
        $map  = $this->map[$this::class] ?? [];
        $gte  = $this->getOperator(GreaterThanOrEqual::class)->getName();
        $not  = $this->getOperator(Not::class)->getDefinition($map, '', true);
        $has  = $this->getScalarType($this->getScalarTypeNode('Has'), false);

        $this->document->setTypeDefinition(Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Where Has condition.
            """
            input {$name} {
                has: {$map[self::TYPE_FLAG]} = yes
                {$not}
                where: [{$type}!]
                count: {$has} = {
                    {$gte}: 1
                }
            }
            DEF,
        ));

        // Return
        return $name;
    }
    // </editor-fold>

    // <editor-fold desc="Defaults">
    // =========================================================================
    protected function addRootTypeDefinitions(): void {
        $this->addTypeDefinitions($this, [
            self::TYPE_FLAG => Parser::enumTypeDefinition(
            /** @lang GraphQL */
                <<<GRAPHQL
                enum {$this->name}TypeFlag {
                    yes
                }
                GRAPHQL,
            ),
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Names">
    // =========================================================================
    protected function getConditionTypeName(InputObjectTypeDefinitionNode $node): string {
        return "{$this->name}Condition{$node->name->value}";
    }

    protected function getScalarTypeName(ScalarTypeDefinitionNode $node, bool $nullable): string {
        return "{$this->name}Scalar{$node->name->value}".($nullable ? 'OrNull' : '');
    }

    protected function getRelationTypeName(InputObjectTypeDefinitionNode $node): string {
        return "{$this->name}Relation{$node->name->value}";
    }

    protected function getScalarOperatorTypeName(
        Operator $operator,
        ScalarTypeDefinitionNode $node = null,
        bool $nullable = null,
    ): string {
        $op   = Str::studly($operator->getName());
        $base = $node ? $this->getScalarTypeName($node, $nullable) : $this->name;
        $name = "{$base}Operator{$op}";

        return $name;
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function isScalar(string $type): bool {
        return isset($this->scalars[$type]);
    }

    /**
     * @param class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator> $class
     */
    protected function getOperator(string $class): Operator {
        $operator = $this->operators[$class] ?? null;

        if (!$operator) {
            $this->operators[$class] = $this->container->make($class);
            $operator                = $this->operators[$class];

            if ($operator instanceof OperatorHasTypes) {
                $this->addTypeDefinitions($operator, $operator->getTypeDefinitions(
                    $this->getScalarOperatorTypeName($operator),
                ));
            }
        }

        return $operator;
    }
    // </editor-fold>

    // <editor-fold desc="AST Helpers">
    // =========================================================================
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

    protected function addTypeDefinition(string $name, TypeDefinitionNode $definition): TypeDefinitionNode {
        if (!$this->isTypeDefinitionExists($name)) {
            $this->document->setTypeDefinition($definition);
        }

        return $this->getTypeDefinitionNode($name);
    }

    /**
     * @param array<\GraphQL\Language\AST\TypeDefinitionNode> $definitions
     */
    protected function addTypeDefinitions(object $owner, array $definitions): void {
        foreach ($definitions as $name => $definition) {
            $fullname                        = $definition->name->value;
            $this->map[$owner::class][$name] = $fullname;

            $this->addTypeDefinition($fullname, $definition);
        }
    }

    protected function getScalarTypeNode(string $scalar): ScalarTypeDefinitionNode {
        return Parser::scalarTypeDefinition("scalar {$scalar}");
    }
    //</editor-fold>
}
