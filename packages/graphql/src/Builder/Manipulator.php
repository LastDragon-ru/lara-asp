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
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeNoOperators;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_filter;
use function array_map;
use function implode;
use function is_object;

// @phpcs:disable Generic.Files.LineLength.TooLong

abstract class Manipulator extends AstManipulator implements TypeProvider {
    public function __construct(
        DirectiveLocator $directives,
        DocumentAST $document,
        TypeRegistry $types,
        private Container $container,
        private BuilderInfo $builderInfo,
        private Operators $operators,
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

    protected function getOperators(): Operators {
        return $this->operators;
    }
    // </editor-fold>

    // <editor-fold desc="TypeProvider">
    // =========================================================================
    public function getType(string $definition, string $type = null, bool $nullable = null): string {
        // Exists?
        $name = $definition::getName($this->getBuilderInfo(), $type, $nullable);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Create new
        $instance = $this->getContainer()->make($definition);
        $node     = $instance->getTypeDefinitionNode($this, $name, $type, $nullable);

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
    // </editor-fold>

    // <editor-fold desc="Operators">
    // =========================================================================
    public function hasTypeOperators(string $type): bool {
        return $this->getOperators()->hasOperators($type);
    }

    /**
     * @return array<Operator>
     */
    public function getTypeOperators(string $type, bool $nullable): array {
        $operators = $this->getOperators()->getOperators($type, $nullable);
        $operators = array_filter($operators, function (Operator $operator): bool {
            return $operator->isBuilderSupported($this->getBuilderInfo()->getBuilder());
        });

        if (!$operators) {
            throw new TypeNoOperators($type);
        }

        return $operators;
    }

    public function getOperatorField(
        Operator $operator,
        InputValueDefinitionNode|TypeDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|Type|string $type,
        string $field = null,
    ): string {
        $type        = is_object($type) ? $this->getNodeName($type) : $type;
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
    public function getOperatorsFields(
        array $operators,
        InputValueDefinitionNode|TypeDefinitionNode|FieldDefinitionNode|InputObjectField|FieldDefinition|Type|string $type,
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
