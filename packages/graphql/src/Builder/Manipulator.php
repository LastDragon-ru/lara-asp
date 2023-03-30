<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\FakeTypeDefinitionIsNotFake;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\FakeTypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionImpossibleToCreateType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionInvalidTypeName;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Source;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Pagination\PaginationType;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_map;
use function array_values;
use function count;
use function implode;
use function mb_strlen;
use function mb_substr;

class Manipulator extends AstManipulator implements TypeProvider {
    /**
     * @var array<class-string<Scope>, Operators>
     */
    private array $operators = [];

    public function __construct(
        DirectiveLocator $directives,
        DocumentAST $document,
        TypeRegistry $types,
        private BuilderInfo $builderInfo,
    ) {
        parent::__construct($directives, $document, $types);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getBuilderInfo(): BuilderInfo {
        return $this->builderInfo;
    }
    // </editor-fold>

    // <editor-fold desc="TypeProvider">
    // =========================================================================
    public function getType(string $definition, TypeSource $source): string {
        // Exists?
        $name = $definition::getTypeName($this, $this->getBuilderInfo(), $source);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Fake
        $this->addFakeTypeDefinition($name);

        // Instance (phpstan is not so smart yet...)
        $instance = Container::getInstance()->make($definition);

        if (!($instance instanceof TypeDefinition)) {
            throw new TypeDefinitionImpossibleToCreateType($definition, $source);
        }

        // Create new
        $node = $instance->getTypeDefinitionNode($this, $name, $source);

        if (!$node) {
            throw new TypeDefinitionImpossibleToCreateType($definition, $source);
        }

        if ($name !== $this->getNodeName($node)) {
            throw new TypeDefinitionInvalidTypeName($definition, $name, $this->getNodeName($node));
        }

        // Save
        $this->removeFakeTypeDefinition($name);
        $this->addTypeDefinition($node);

        // Return
        return $name;
    }

    public function getTypeSource(
        TypeDefinitionNode|NamedTypeNode|ListTypeNode|NonNullTypeNode|Type $type,
    ): TypeSource {
        $source = null;

        if ($type instanceof InputObjectTypeDefinitionNode || $type instanceof InputObjectType) {
            $source = new InputSource($this, $type);
        } elseif ($type instanceof ObjectTypeDefinitionNode || $type instanceof ObjectType) {
            $source = new ObjectSource($this, $type);
        } elseif ($type instanceof InterfaceTypeDefinitionNode || $type instanceof InterfaceType) {
            $source = new InterfaceSource($this, $type);
        } else {
            $source = new Source($this, $type);
        }

        return $source;
    }
    // </editor-fold>

    // <editor-fold desc="Operators">
    // =========================================================================
    public function addOperators(Operators $operators): static {
        $this->operators[$operators->getScope()] = $operators;

        return $this;
    }

    /**
     * @template T of Operator
     *
     * @param class-string<Scope> $scope
     * @param class-string<T>     $operator
     *
     * @return T
     */
    public function getOperator(string $scope, string $operator): Operator {
        return Container::getInstance()->make($operator);
    }

    /**
     * Method doesn't check Builder!
     *
     * @param class-string<Scope> $scope
     */
    public function hasTypeOperators(string $scope, string $type): bool {
        return (bool) ($this->operators[$scope] ?? null)?->hasOperators($type);
    }

    /**
     * @param class-string<Scope> $scope
     *
     * @return list<Operator>
     */
    public function getTypeOperators(string $scope, string $type, bool $nullable = false): array {
        // Provider?
        $provider = $this->operators[$scope] ?? null;

        if (!$provider) {
            return [];
        }

        // Operators
        $unique    = [];
        $builder   = $this->getBuilderInfo()->getBuilder();
        $operators = [
            ...($provider->hasOperators($type) ? $provider->getOperators($type) : []),
            ...($nullable && $provider->hasOperators(Operators::Null) ? $provider->getOperators(Operators::Null) : []),
        ];

        foreach ($operators as $operator) {
            if (isset($unique[$operator::class])) {
                continue;
            }

            if (!$operator->isBuilderSupported($builder)) {
                continue;
            }

            $unique[$operator::class] = $operator;
        }

        $unique = array_values($unique);

        // Return
        return $unique;
    }

    public function getOperatorField(
        Operator $operator,
        TypeSource $source,
        ?string $field,
        ?string $description = null,
    ): string {
        $type        = $operator->getFieldType($this, $source);
        $field       = $field ?: $operator::getName();
        $directive   = $operator->getFieldDirective() ?? $operator::getDirectiveName();
        $directive   = $directive instanceof DirectiveNode
            ? Printer::doPrint($directive)
            : $directive;
        $description = $description ?: $operator->getFieldDescription();

        return <<<DEF
            """
            {$description}
            """
            {$field}: {$type}
            {$directive}
        DEF;
    }

    /**
     * @param array<Operator> $operators
     */
    public function getOperatorsFields(array $operators, TypeSource $source): string {
        return implode(
            "\n",
            array_map(
                function (Operator $operator) use ($source): string {
                    return $this->getOperatorField($operator, $source, null);
                },
                $operators,
            ),
        );
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =================================================================================================================
    protected function addFakeTypeDefinition(string $name): void {
        $this->addTypeDefinition(
            Parser::inputObjectTypeDefinition(
                <<<DEF
                """
                Fake type to prevent circular dependency infinite loop.
                """
                input {$name} {
                    fake: Boolean! = true
                }
                DEF,
            ),
        );
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

    public function getPlaceholderTypeDefinitionNode(
        FieldDefinitionNode|FieldDefinition $field,
    ): TypeDefinitionNode|Type|null {
        $node     = null;
        $paginate = $this->getNodeDirective($field, PaginateDirective::class);

        if ($paginate) {
            $type       = $this->getNodeTypeName($this->getTypeDefinitionNode($field));
            $pagination = (new class() extends PaginateDirective {
                public function getPaginationType(PaginateDirective $directive): PaginationType {
                    return $directive->paginationType();
                }
            })->getPaginationType($paginate);

            if ($pagination->isPaginator()) {
                $type = mb_substr($type, 0, -mb_strlen('Paginator'));
            } elseif ($pagination->isSimple()) {
                $type = mb_substr($type, 0, -mb_strlen('SimplePaginator'));
            } elseif ($pagination->isConnection()) {
                $type = mb_substr($type, 0, -mb_strlen('Connection'));
            } else {
                // empty
            }

            if ($type) {
                $node = $this->getTypeDefinitionNode($type);
            }
        } else {
            $node = $this->getTypeDefinitionNode($field);
        }

        return $node;
    }
    // </editor-fold>
}
