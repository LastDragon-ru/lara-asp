<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\AstManipulator as BaseAstManipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypes;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypesForScalar;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypesForScalarNullable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorNegationable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;
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

class AstManipulator extends BaseAstManipulator {
    /**
     * @var array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>,\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator|null>
     */
    protected array $operators = [];

    /**
     * @param array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>> $scalars
     * @param array<string,string>                                                                           $aliases
     */
    public function __construct(
        DocumentAST $document,
        protected Container $container,
        protected string $name,
        protected array $scalars,
        protected array $aliases,
    ) {
        parent::__construct($document);
    }

    // <editor-fold desc="API">
    // =========================================================================
    public function getType(InputValueDefinitionNode $node): ListTypeNode {
        $type = null;

        if (!($node->type instanceof ListTypeNode)) {
            $def = $this->getTypeDefinitionNode($node);

            if ($def instanceof InputObjectTypeDefinitionNode) {
                $name = $this->getInputType($def);
                $type = Parser::typeReference("[{$name}!]");
            }
        } else {
            $type = $node->type;
        }

        if (!($type instanceof ListTypeNode)) {
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
        $operators = $this->getScalarOperators(SearchByDirective::Logic, false);
        $scalar    = $this->getScalarTypeNode($name);
        $content   = implode("\n", array_map(function (string $operator) use ($scalar): string {
            return $this->getOperatorType($this->getOperator($operator), $scalar, false);
        }, $operators));
        $type      = $this->addTypeDefinition($name, Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available conditions.
            """
            input {$name} {
                {$content}
            }
            DEF,
        ));

        // Add searchable fields
        $description = Parser::description('"""Property condition."""');

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
                $fieldTypeNode = $this->getScalarTypeNode($fieldType);
            }

            if ($fieldTypeNode instanceof InputObjectTypeDefinitionNode) {
                $fieldDefinition = $this->getRelationType($fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof ScalarTypeDefinitionNode) {
                $fieldDefinition = $this->getScalarType($fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof EnumTypeDefinitionNode) {
                $fieldDefinition = $this->getEnumType($fieldTypeNode, $fieldNullable);
            } else {
                // empty
            }

            // Create new Field
            if ($fieldDefinition) {
                // TODO [SearchBy] We probably not need all directives from the
                //      original Input type, but cloning is the easiest way...
                $type->fields[] = tap(
                    $field->cloneDeep(),
                    static function (InputValueDefinitionNode $field) use ($fieldDefinition, $description): void {
                        $field->type        = Parser::typeReference($fieldDefinition);
                        $field->description = $description;
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

    protected function getEnumType(EnumTypeDefinitionNode $node, bool $nullable): string {
        // Exists?
        $name = $this->getEnumTypeName($node, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Determine supported operators
        $type      = $node->name->value;
        $operators = $this->getEnumOperators($nullable);

        // Add type
        $content = implode("\n", array_map(function (string $operator) use ($node, $nullable): string {
            return $this->getOperatorType($this->getOperator($operator), $node, $nullable);
        }, $operators));

        $this->addTypeDefinition($name, Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available operators for enum {$type} (only one operator allowed at a time).
            """
            input {$name} {
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

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Determine supported operators
        $type      = $node->name->value;
        $operators = $this->getScalarOperators($type, $nullable);

        // Add type
        $scalar  = $this->getScalarRealTypeNode($node);
        $content = implode("\n", array_map(function (string $operator) use ($scalar, $nullable): string {
            return $this->getOperatorType($this->getOperator($operator), $scalar, $nullable);
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

    protected function getOperatorType(
        Operator $operator,
        ScalarTypeDefinitionNode|EnumTypeDefinitionNode $node,
        bool $nullable,
    ): string {
        // Add types for Scalars
        if ($operator instanceof OperatorHasTypesForScalar) {
            $this->addTypeDefinitions($operator, $operator->getTypeDefinitionsForScalar(
                $this->getOperatorTypeName($operator, $node, false),
                $node->name->value,
            ));
        }

        if ($operator instanceof OperatorHasTypesForScalarNullable) {
            $this->addTypeDefinitions($operator, $operator->getTypeDefinitionsForScalar(
                $this->getOperatorTypeName($operator, $node, $nullable),
                $node->name->value,
                $nullable,
            ));
        }

        // Named map
        $map  = array_merge(
            $this->getMap($operator),
            $this->getMap($this),
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
        $input     = $this->getScalarTypeNode($this->getInputType($node));
        $scalar    = $this->getScalarRealTypeNode($this->getScalarTypeNode(SearchByDirective::RelationHas));
        $operators = $this->getScalarOperators(SearchByDirective::Relation, false);
        $content   = implode("\n", array_map(function (string $operator) use ($input, $scalar): string {
            $operator = $this->getOperator($operator);
            $node     = $operator instanceof Relation ? $input : $scalar;
            $type     = $this->getOperatorType($operator, $node, false);

            return $type;
        }, $operators));

        $this->document->setTypeDefinition(Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Relation condition.
            """
            input {$name} {
                {$content}
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
        $flag = SearchByDirective::TypeFlag;

        $this->addTypeDefinitions($this, [
            $flag => Parser::enumTypeDefinition(
            /** @lang GraphQL */
                <<<GRAPHQL
                enum {$this->name}{$flag} {
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

    protected function getEnumTypeName(EnumTypeDefinitionNode $node, bool $nullable): string {
        return "{$this->name}Enum{$node->name->value}".($nullable ? 'OrNull' : '');
    }

    protected function getScalarTypeName(ScalarTypeDefinitionNode $node, bool $nullable): string {
        return "{$this->name}Scalar{$node->name->value}".($nullable ? 'OrNull' : '');
    }

    protected function getRelationTypeName(InputObjectTypeDefinitionNode $node): string {
        return "{$this->name}Relation{$node->name->value}";
    }

    protected function getOperatorTypeName(
        Operator $operator,
        ScalarTypeDefinitionNode|EnumTypeDefinitionNode $node = null,
        bool $nullable = null,
    ): string {
        $op   = Str::studly($operator->getName());
        $base = $this->name;

        if ($node instanceof ScalarTypeDefinitionNode) {
            $base = $this->getScalarTypeName($node, $nullable);
        } elseif ($node instanceof EnumTypeDefinitionNode) {
            $base = $this->getEnumTypeName($node, $nullable);
        } else {
            // empty
        }

        return "{$base}Operator{$op}";
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function isScalar(string $type): bool {
        return isset($this->scalars[$type]);
    }

    /**
     * @return array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>
     */
    protected function getEnumOperators(bool $nullable): array {
        return $this->getScalarOperators(SearchByDirective::Enum, $nullable);
    }

    /**
     * @return array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>
     */
    protected function getScalarOperators(string $scalar, bool $nullable): array {
        $operators = $scalar;

        do {
            $operators = $this->scalars[$operators] ?? [];
        } while (!is_array($operators));

        if (empty($operators)) {
            throw new SearchByException(sprintf(
                'Generated scalar type is empty. Please check definition for `%s` scalar.',
                $scalar,
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

        // Return
        return $operators;
    }

    /**
     * @param class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator> $class
     */
    protected function getOperator(string $class): Operator {
        $operator = $this->operators[$class] ?? null;

        if (!$operator) {
            $this->operators[$class] = $this->container->make($class);
            $operator                = $this->operators[$class];

            if ($operator instanceof OperatorHasTypes) {
                $this->addTypeDefinitions($operator, $operator->getTypeDefinitions(
                    $this->getOperatorTypeName($operator),
                ));
            }
        }

        return $operator;
    }

    protected function getScalarRealTypeNode(ScalarTypeDefinitionNode $node): ScalarTypeDefinitionNode {
        return isset($this->aliases[$node->name->value])
            ? $this->getScalarTypeNode($this->aliases[$node->name->value])
            : $node;
    }
    // </editor-fold>
}
