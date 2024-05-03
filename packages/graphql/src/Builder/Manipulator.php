<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\BlockString;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\FakeTypeDefinitionIsNotFake;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\FakeTypeDefinitionUnknown;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorImpossibleToCreateField;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionImpossibleToCreateType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionInvalidTypeName;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Source;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use LastDragon_ru\LaraASP\GraphQL\Utils\TypeReference;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Override;

use function array_map;
use function array_unshift;
use function count;
use function implode;
use function is_string;

class Manipulator extends AstManipulator implements TypeProvider {
    public function __construct(
        protected readonly ContainerResolver $container,
        DirectiveLocator $directiveLocator,
        DocumentAST $document,
        TypeRegistry $types,
    ) {
        parent::__construct($directiveLocator, $document, $types);
    }

    // <editor-fold desc="TypeProvider">
    // =========================================================================
    #[Override]
    public function getType(string $definition, TypeSource $source, Context $context): string {
        // Exists?
        $instance = $this->container->getInstance()->make($definition);
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

        if (is_string($node)) {
            $node = new TypeReference($name, $node);
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
    public function getTypeSource(TypeDefinitionNode|TypeNode|Type $type): TypeSource {
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
    /**
     * @template T of Operator
     *
     * @param Node|(TypeDefinitionNode&Node)|Type|InputObjectField|FieldDefinition|Argument $node
     * @param class-string<T>                                                               $operator
     *
     * @return (T&Directive)|null
     */
    public function getOperatorDirective(
        Node|TypeDefinitionNode|Type|InputObjectField|FieldDefinition|Argument $node,
        string $operator,
        TypeSource $source,
        Context $context,
    ): ?Operator {
        // Operators?
        $provider = $context->get(HandlerContextOperators::class)?->value;

        if (!$provider) {
            return null;
        }

        // Search
        $instance   = null;
        $directives = $this->getDirectives($node, $operator);

        foreach ($directives as $directive) {
            $directive = $provider->getOperator($this, $directive, $source, $context);

            if ($directive) {
                $instance = $directive;
                break;
            }
        }

        return $instance;
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

        // Type?
        $type = $operator->getFieldType($this, $source, $context);

        if (!$type) {
            throw new OperatorImpossibleToCreateField($operator, $source, $context);
        }

        // Definition
        $field       = $field ?: $operator::getName();
        $directives  = implode(
            "\n",
            array_map(
                Printer::doPrint(...),
                $directives,
            ),
        );
        $description = $description ?: $operator->getFieldDescription();
        $description = BlockString::print((string) $description);

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
        $fields = [];

        foreach ($operators as $operator) {
            if (!isset($fields[$operator::class])) {
                $fields[$operator::class] = $this->getOperatorField($operator, $source, $context, null);
            }
        }

        return implode("\n", $fields);
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
    // </editor-fold>
}
