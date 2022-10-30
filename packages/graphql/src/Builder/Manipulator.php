<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionImpossibleToCreateType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionInvalidTypeName;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_map;
use function implode;

abstract class Manipulator extends AstManipulator implements TypeProvider {
    public function __construct(
        DirectiveLocator $directives,
        DocumentAST $document,
        TypeRegistry $types,
        private Container $container,
        private BuilderInfo $builderInfo,
    ) {
        parent::__construct($directives, $document, $types);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    protected function getContainer(): Container {
        return $this->container;
    }

    protected function getBuilderInfo(): BuilderInfo {
        return $this->builderInfo;
    }
    // </editor-fold>

    // <editor-fold desc="TypeProvider">
    // =========================================================================
    public function getType(string $definition, string $type = null, bool $nullable = null): string {
        // Exists?
        $name = $this->getTypeName($definition::getName(), $type, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Create new
        $instance = $this->getContainer()->make($definition);
        $node     = $instance->getTypeDefinitionNode($name, $type, $nullable);

        if (!$node) {
            throw new TypeDefinitionImpossibleToCreateType($definition, $type, $nullable);
        }

        if ($name !== $this->getNodeName($node)) {
            throw new TypeDefinitionInvalidTypeName($definition, $name, $this->getNodeName($node));
        }

        // Save
        $this->addTypeDefinition($node);

        // Return
        return $name;
    }

    abstract protected function getTypeName(string $name, string $type = null, bool $nullable = null): string;
    // </editor-fold>

    // <editor-fold desc="Operators">
    // =========================================================================
    protected function getOperatorField(
        Operator $operator,
        InputValueDefinitionNode|TypeDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|Type $type,
        string $field = null,
    ): string {
        $type        = $this->getNodeName($type);
        $type        = $operator->getFieldType($this, $type) ?? $type;
        $field       = $field ?: $operator::getName();
        $directive   = $operator->getFieldDirective() ?? $operator::getDirectiveName();
        $directive   = $directive instanceof DirectiveNode
            ? Printer::doPrint($directive)
            : $directive;
        $description = $operator->getFieldDescription();

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
    protected function getOperatorsFields(
        array $operators,
        InputValueDefinitionNode|TypeDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|Type $type,
    ): string {
        return implode(
            "\n",
            array_map(
                function (Operator $operator) use ($type): string {
                    return $this->getOperatorField($operator, $type);
                },
                $operators,
            ),
        );
    }
    // </editor-fold>
}
