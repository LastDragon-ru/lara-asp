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
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\AstManipulator as BaseAstManipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypes;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypesForScalar;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypesForScalarNullable;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\OperatorDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\RelationOperatorDirective;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;

use function array_map;
use function array_merge;
use function array_shift;
use function implode;
use function is_a;
use function is_null;
use function json_encode;
use function sprintf;
use function tap;

class AstManipulator extends BaseAstManipulator {
    protected const PropertyOperators = Directive::Name.'Operators';

    protected Cache $cache;

    /**
     * @var array<class-string<
     *      \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator|
     *      \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator
     *      >>
     */
    protected array $operators;

    public function __construct(
        DirectiveLocator $directives,
        DocumentAST $document,
        protected Container $container,
        protected Types $types,
    ) {
        parent::__construct($directives, $document);

        $this->reset();
    }

    // <editor-fold desc="Update">
    // =========================================================================
    public function update(DirectiveNode $directive, InputValueDefinitionNode $node): void {
        // Reset
        $this->reset();

        // Transform
        if (!isset($node->type->{self::PropertyOperators})) {
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

            // Update
            $node->type                            = $type;
            $node->type->{self::PropertyOperators} = $this->operators;
        }

        // Update
        $this->updateDirective($directive, [
            Directive::ArgOperators => $node->type->{self::PropertyOperators},
        ]);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    protected function updateDirective(DirectiveNode $directive, array $arguments): void {
        foreach ($arguments as $name => $value) {
            $directive->arguments[] = Parser::constArgument($name.': '.json_encode($this->operators));
        }
    }

    protected function reset(): void {
        $this->cache     = new Cache();
        $this->operators = [];
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
        $operators = $this->getScalarOperators(Directive::ScalarLogic, false);
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

            if (is_null($fieldTypeNode)) {
                $fieldTypeNode = $this->getScalarTypeNode($fieldType);
            }

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

    public function getEnumType(EnumTypeDefinitionNode $type, bool $nullable): string {
        // Exists?
        $name = $this->getEnumTypeName($type, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Determine supported operators
        $enum      = $type->name->value;
        $operators = $this->getEnumOperators($enum, $nullable);

        // Add type
        $content = implode("\n", array_map(function (string $operator) use ($type, $nullable): string {
            return $this->getOperatorType($this->getOperator($operator), $type, $nullable);
        }, $operators));

        $this->addTypeDefinition($name, Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available operators for enum {$enum} (only one operator allowed at a time).
            """
            input {$name} {
                {$content}
            }
            DEF,
        ));

        // Return
        return $name;
    }

    public function getScalarType(ScalarTypeDefinitionNode $type, bool $nullable): string {
        // Exists?
        $name = $this->getScalarTypeName($type, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Determine supported operators
        $scalar    = $type->name->value;
        $operators = $this->getScalarOperators($scalar, $nullable);

        // Add type
        $mark    = $nullable ? '' : '!';
        $content = implode("\n", array_map(function (string $operator) use ($type, $nullable): string {
            return $this->getOperatorType($this->getOperator($operator), $type, $nullable);
        }, $operators));

        $this->addTypeDefinition($name, Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Available operators for scalar {$scalar}{$mark} (only one operator allowed at a time).
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

    protected function getComplexType(
        InputValueDefinitionNode $field,
        InputObjectTypeDefinitionNode $type,
        bool $nullable,
    ): string {
        // Exists?
        $operator = $this->getComplexOperator($field, $type);
        $name     = $this->getComplexTypeName($type, $operator);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Create
        $definition = $operator->getDefinition($this, $field, $type, $name, $nullable);

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
    /**
     * @return array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>
     */
    protected function getEnumOperators(string $enum, bool $nullable): array {
        $operators = $this->types->getEnumOperators($enum, $nullable);

        if (empty($operators)) {
            throw new SearchByException(sprintf(
                'List of operators for enum `%s` is empty.',
                $enum,
            ));
        }

        return $operators;
    }

    /**
     * @return array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>
     */
    protected function getScalarOperators(string $scalar, bool $nullable): array {
        $operators = $this->types->getScalarOperators($scalar, $nullable);

        if (empty($operators)) {
            throw new SearchByException(sprintf(
                'List of operators for scalar `%s` is empty.',
                $scalar,
            ));
        }

        return $operators;
    }

    /**
     * @param class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator> $class
     */
    protected function getOperator(string $class): Operator {
        return $this->cache->get($class, function () use ($class): Operator {
            // Is operator?
            if (!is_a($class, Operator::class, true)) {
                throw new SearchByException(sprintf(
                    'Operator `%s` must implement `%s`.',
                    $class,
                    Operator::class,
                ));
            }

            // Create Instance
            $operator = $this->container->make($class);

            // Add types
            if ($operator instanceof OperatorHasTypes) {
                $this->addTypeDefinitions($operator, $operator->getTypeDefinitions(
                    $this->getOperatorTypeName($operator),
                ));
            }

            // Remember
            $this->operators[] = $operator::class;

            // Return
            return $operator;
        });
    }

    protected function getComplexOperator(
        InputValueDefinitionNode|InputObjectTypeDefinitionNode ...$nodes,
    ): ComplexOperator {
        // Class
        $class = null;

        do {
            $node  = array_shift($nodes);
            $class = $this->cache->get(
                [__FUNCTION__, $node],
                function () use ($node): ?string {
                    $default   = $this->container->make(RelationOperatorDirective::class);
                    $directive = $node
                        ? $this->getNodeDirective($node, OperatorDirective::class)
                        : null;
                    $class     = $directive instanceof OperatorDirective
                        ? $directive->getClass()
                        : $default->getClass();

                    return $class;
                },
            );
        } while ($node && is_null($class));

        // Operator
        $operator = $this->cache->get($class, function () use ($class): ComplexOperator {
            // Is complex operator?
            if (!is_a($class, ComplexOperator::class, true)) {
                throw new SearchByException(sprintf(
                    'Operator `%s` must implement `%s`.',
                    $class,
                    ComplexOperator::class,
                ));
            }

            // Create Instance
            $operator = $this->container->make($class);

            // Add types
            if ($operator instanceof OperatorHasTypes) {
                $this->addTypeDefinitions($operator, $operator->getTypeDefinitions(
                    $this->getOperatorTypeName($operator),
                ));
            }

            // Remember
            $this->operators[] = $operator::class;

            // Return
            return $operator;
        });

        // Return
        return $operator;
    }
    // </editor-fold>
}
