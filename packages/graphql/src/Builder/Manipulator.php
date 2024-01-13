<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\BlockString;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contexts\AstManipulationBuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorsDirective;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\FakeTypeDefinitionIsNotFake;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\FakeTypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionImpossibleToCreateType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionInvalidTypeName;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Source;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive as StreamDirective;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Pagination\PaginationType;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Override;

use function array_map;
use function array_push;
use function array_unshift;
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

    // <editor-fold desc="TypeProvider">
    // =========================================================================
    #[Override]
    public function getType(string $definition, TypeSource $source, Context $context): string {
        // Exists?
        $instance = Container::getInstance()->make($definition);
        $name     = $instance->getTypeName($source, $context);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Fake
        $this->addFakeTypeDefinition($name);

        // Create new
        $node = $instance->getTypeDefinition($this, $source, $context, $name);

        if (!$node) {
            throw new TypeDefinitionImpossibleToCreateType($definition, $source, $context);
        }

        if ($name !== $this->getName($node)) {
            throw new TypeDefinitionInvalidTypeName($definition, $name, $this->getName($node), $context);
        }

        // Save
        $this->removeFakeTypeDefinition($name);
        $this->addTypeDefinition($node);

        // Return
        return $name;
    }

    #[Override]
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
     * @param class-string<Scope> $scope
     *
     * @return list<Operator>
     */
    public function getTypeOperators(string $scope, string $type, Context $context, string ...$extras): array {
        // Provider?
        $provider = $this->operators[$scope] ?? null;

        if (!$provider) {
            return [];
        }

        // Builder?
        $builder = $context->get(AstManipulationBuilderInfo::class)?->value->getBuilder();

        if (!$builder) {
            return [];
        }

        // Operators
        $operators = [];

        if ($this->isTypeDefinitionExists($type)) {
            $node       = $this->getTypeDefinition($type);
            $directives = $this->getDirectives($node, $scope);

            foreach ($directives as $directive) {
                if ($directive instanceof OperatorsDirective) {
                    $directiveType = $directive->getType();

                    if ($type !== $directiveType) {
                        array_push($operators, ...$this->getTypeOperators($scope, $directiveType, $context));
                    } else {
                        array_push($operators, ...$provider->getOperators($type));
                    }
                } elseif ($directive instanceof Operator) {
                    $operators[] = $directive;
                } else {
                    // empty
                }
            }
        }

        if (!$operators && $provider->hasOperators($type)) {
            array_push($operators, ...$provider->getOperators($type));
        }

        if (!$operators) {
            return [];
        }

        // Extra
        foreach ($extras as $extra) {
            array_push($operators, ...$this->getTypeOperators($scope, $extra, $context));
        }

        // Unique
        $unique = [];

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

    /**
     * @param list<DirectiveNode> $directives
     */
    public function getOperatorField(
        Operator $operator,
        TypeSource $source,
        Context $context,
        ?string $field,
        ?string $description = null,
        array $directives = [],
    ): string {
        // Operator already added?
        $added   = false;
        $locator = $this->getDirectiveLocator();

        foreach ($directives as $directive) {
            if ($locator->resolve($directive->name->value) === $operator::class) {
                $added = true;
                break;
            }
        }

        if (!$added) {
            array_unshift($directives, Parser::directive('@'.DirectiveLocator::directiveName($operator::class)));
        }

        // Definition
        $type        = $operator->getFieldType($this, $source, $context);
        $field       = $field ?: $operator::getName();
        $directives  = implode(
            "\n",
            array_map(
                Printer::doPrint(...),
                $directives,
            ),
        );
        $description = $description ?: $operator->getFieldDescription();
        $description = BlockString::print($description);

        return <<<GRAPHQL
            {$description}
            {$field}: {$type}
            {$directives}
        GRAPHQL;
    }

    /**
     * @param list<Operator> $operators
     */
    public function getOperatorsFields(array $operators, TypeSource $source, Context $context): string {
        return implode(
            "\n",
            array_map(
                function (Operator $operator) use ($source, $context): string {
                    return $this->getOperatorField($operator, $source, $context, null);
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
                <<<GRAPHQL
                """
                Fake type to prevent circular dependency infinite loop.
                """
                input {$name} {
                    fake: Boolean! = true
                }
                GRAPHQL,
            ),
        );
    }

    protected function removeFakeTypeDefinition(string $name): void {
        // Possible?
        $fake = $this->getTypeDefinition($name);

        if (!($fake instanceof InputObjectTypeDefinitionNode)) {
            throw new FakeTypeDefinitionUnknown($name);
        }

        if (count($fake->fields) !== 1 || $this->getName($fake->fields[0]) !== 'fake') {
            throw new FakeTypeDefinitionIsNotFake($name);
        }

        // Remove
        $this->removeTypeDefinition($name);
    }

    /**
     * @return (TypeDefinitionNode&Node)|Type|null
     */
    public function getPlaceholderTypeDefinitionNode(
        FieldDefinitionNode|FieldDefinition $field,
    ): TypeDefinitionNode|Type|null {
        $node       = $this->getTypeDefinition($field);
        $name       = $this->getTypeName($node);
        $directives = [
            StreamDirective::class,
            PaginateDirective::class,
        ];

        foreach ($directives as $directive) {
            $directive = $this->getDirective($field, $directive);
            $type      = null;

            if ($directive instanceof StreamDirective) {
                $type = Str::singular(mb_substr($name, 0, -mb_strlen(StreamDirective::Name)));
            } elseif ($directive instanceof PaginateDirective) {
                $pagination = (new class() extends PaginateDirective {
                    public function getPaginationType(PaginateDirective $directive): PaginationType {
                        return $directive->paginationType();
                    }
                })->getPaginationType($directive);

                if ($pagination->isPaginator()) {
                    $type = mb_substr($name, 0, -mb_strlen('Paginator'));
                } elseif ($pagination->isSimple()) {
                    $type = mb_substr($name, 0, -mb_strlen('SimplePaginator'));
                } elseif ($pagination->isConnection()) {
                    $type = mb_substr($name, 0, -mb_strlen('Connection'));
                } else {
                    // empty
                }
            } else {
                 // empty
            }

            if ($type) {
                $node = $this->getTypeDefinition($type);

                break;
            }
        }

        return $node;
    }
    // </editor-fold>
}
