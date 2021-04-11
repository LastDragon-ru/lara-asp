<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Core\Concerns\InstanceCache;
use LastDragon_ru\LaraASP\GraphQL\AstManipulator as BaseAstManipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypes;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypesForScalar;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypesForScalarNullable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\OperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;

use function array_map;
use function array_merge;
use function array_push;
use function implode;
use function is_array;
use function is_null;
use function sprintf;
use function str_starts_with;
use function tap;

class AstManipulator extends BaseAstManipulator {
    use InstanceCache;

    /**
     * @param array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>> $scalars
     * @param array<string,string>                                                                           $complex
     */
    public function __construct(
        DirectiveLocator $directives,
        DocumentAST $document,
        protected Container $container,
        protected array $scalars,
        protected array $complex,
    ) {
        parent::__construct($directives, $document);
    }

    // <editor-fold desc="API">
    // =========================================================================
    public function getType(InputValueDefinitionNode $node): NamedTypeNode {
        // Transformed?
        if (str_starts_with(ASTHelper::getUnderlyingTypeName($node), Directive::Name)) {
            return $node->type;
        }

        // Transform
        $type = null;
        $def  = $this->getTypeDefinitionNode($node);

        if ($def instanceof InputObjectTypeDefinitionNode) {
            $name = $this->getInputType($def);
            $type = Parser::typeReference($name);
        }

        if (!($type instanceof NamedTypeNode)) {
            throw new SearchByException(sprintf(
                'Impossible to create Search Condition for `%s`.',
                $node->name->value,
            ));
        }

        // Return
        return $type;
    }
    // </editor-fold>

    // <editor-fold desc="Types">
    // =========================================================================
    public function getInputType(InputObjectTypeDefinitionNode $node): string {
        // Exists?
        $name = $this->getConditionTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add type
        $operators = $this->getScalarOperators(Directive::Logic, false);
        $scalar    = $this->getScalarTypeNode($name);
        $content   = implode("\n", array_map(function (string $operator) use ($scalar): string {
            return $this->getOperatorType($this->getOperator($operator), $scalar, false);
        }, $operators));
        $type      = $this->addTypeDefinition($name, Parser::inputObjectTypeDefinition(
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

            if ($fieldTypeNode instanceof ScalarTypeDefinitionNode) {
                $fieldDefinition = $this->getScalarType($fieldTypeNode, $fieldNullable);
            } elseif ($fieldTypeNode instanceof InputObjectTypeDefinitionNode) {
                $fieldDefinition = $this->getComplexType($fieldTypeNode, $fieldNullable);
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

    public function getEnumType(EnumTypeDefinitionNode $node, bool $nullable): string {
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

    public function getScalarType(ScalarTypeDefinitionNode $node, bool $nullable): string {
        // Exists?
        $name = $this->getScalarTypeName($node, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Determine supported operators
        $type      = $node->name->value;
        $operators = $this->getScalarOperators($type, $nullable);

        // Add type
        $mark    = $nullable ? '' : '!';
        $scalar  = $node;
        $content = implode("\n", array_map(function (string $operator) use ($scalar, $nullable): string {
            return $this->getOperatorType($this->getOperator($operator), $scalar, $nullable);
        }, $operators));

        $this->addTypeDefinition($name, Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available operators for scalar {$type}{$mark} (only one operator allowed at a time).
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

    protected function getComplexType(InputObjectTypeDefinitionNode $node, bool $nullable): string {
        // Exists?
        $operator = $this->getComplexOperator($node);
        $name     = $this->getComplexTypeName($node, $operator);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Create
        $definition = $operator->getDefinition($this, $node, $name, $nullable);

        if ($name !== $definition->name->value) {
            throw new SearchByException(sprintf(
                'Generated type for complex operator `%s` must be named as `%s`, but its name is `%s`.',
                $operator::class,
                $name,
                $definition->name->value,
            ));
        }

        $this->addTypeDefinition($name, $definition);

        // Return
        return $name;
    }
    // </editor-fold>

    // <editor-fold desc="Defaults">
    // =========================================================================
    protected function addRootTypeDefinitions(): void {
        $name = Directive::Name;
        $flag = Directive::TypeFlag;

        $this->addTypeDefinitions($this, [
            $flag => Parser::enumTypeDefinition(
            /** @lang GraphQL */
                <<<GRAPHQL
                """
                Flag.
                """
                enum {$name}{$flag} {
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

    protected function getOperatorTypeName(
        Operator|ComplexOperator $operator,
        ScalarTypeDefinitionNode|EnumTypeDefinitionNode $node = null,
        bool $nullable = null,
    ): string {
        $op   = Str::studly($operator->getName());
        $base = Directive::Name;

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
        return $this->getScalarOperators(Directive::Enum, $nullable);
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
                'Generated scalar type is empty. Please check operators for `%s` scalar.',
                $scalar,
            ));
        }

        // Add `null` for nullable
        if ($nullable) {
            array_push($operators, ...($this->scalars[Directive::Null] ?? []));
        }

        // Return
        return $operators;
    }

    /**
     * @param class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator> $class
     */
    protected function getOperator(string $class): Operator {
        return $this->instanceCacheGet([__FUNCTION__, $class], function () use ($class): Operator {
            $operator = $this->container->make($class);

            if (!($operator instanceof Operator)) {
                throw new SearchByException(sprintf(
                    'Operator `%s` must implement `%s`.',
                    $class,
                    Operator::class,
                ));
            }

            if ($operator instanceof OperatorHasTypes) {
                $this->addTypeDefinitions($operator, $operator->getTypeDefinitions(
                    $this->getOperatorTypeName($operator),
                ));
            }

            return $operator;
        });
    }

    protected function getComplexOperator(InputObjectTypeDefinitionNode $node): ComplexOperator {
        return $this->instanceCacheGet([__FUNCTION__, $node->name->value], function () use ($node): ComplexOperator {
            // Determine operator class
            $class     = Relation::class;
            $directive = $this->getNodeDirective($node, OperatorDirective::class);

            if ($directive instanceof OperatorDirective) {
                $class = $this->complex[$directive->getName()] ?? null;

                if (!$class) {
                    throw new SearchByException(sprintf(
                        'Complex operator `%s` not found. Please check operator list in package config.',
                        $directive->getName(),
                    ));
                }
            }

            // Create Instance
            $operator = $this->container->make($class);

            if (!($operator instanceof ComplexOperator)) {
                throw new SearchByException(sprintf(
                    'Operator `%s` must implement `%s`.',
                    $class,
                    ComplexOperator::class,
                ));
            }

            // Add types
            if ($operator instanceof OperatorHasTypes) {
                $this->addTypeDefinitions($operator, $operator->getTypeDefinitions(
                    $this->getOperatorTypeName($operator),
                ));
            }

            // Return
            return $operator;
        });
    }
    // </editor-fold>
}
