<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeExtensionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\AST;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionIsNotScalar;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionIsNotScalarExtension;
use LastDragon_ru\LaraASP\GraphQL\Builder\Scalars\Internal;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Events\BuildSchemaString;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Directives\ScalarDirective;
use Nuwave\Lighthouse\Support\Contracts\TypeManipulator;
use Override;

use function array_key_exists;
use function count;
use function implode;

/**
 * Modifies the Schema for Directive.
 *
 * We are using special scalars to add operators. The directive provides a way
 * to add and extend them. Extending is required because Lighthouse doesn't
 * support adding directives from extensions nodes yet.
 *
 * @see https://github.com/nuwave/lighthouse/issues/2509
 * @see https://github.com/nuwave/lighthouse/pull/2512
 */
abstract class SchemaDirective extends BaseDirective implements TypeManipulator {
    use WithManipulator;

    #[Override]
    public static function definition(): string {
        $name      = DirectiveLocator::directiveName(static::class);
        $locations = implode(' | ', [DirectiveLocation::SCALAR]);

        return <<<GRAPHQL
            """
            Extends schema for Directive.
            """
            directive @{$name} on {$locations}
        GRAPHQL;
    }

    public function __invoke(BuildSchemaString $event): string {
        $name      = DirectiveLocator::directiveName(static::class);
        $scalar    = $this->getScalarDefinition(Str::studly($name));
        $directive = "@{$name}";

        return "{$scalar} {$directive}";
    }

    #[Override]
    public function manipulateTypeDefinition(DocumentAST &$documentAST, TypeDefinitionNode &$typeDefinition): void {
        // Prepare
        $manipulator = $this->getAstManipulator($documentAST);
        $scalars     = $this->getScalars($manipulator);

        // Redefine user-added scalars
        foreach ($documentAST->types as $type => $definition) {
            // Supported?
            if (!array_key_exists($type, $scalars)) {
                continue;
            }

            // Valid?
            if (!($definition instanceof ScalarTypeDefinitionNode)) {
                throw new TypeDefinitionIsNotScalar($type, $manipulator->getTypeFullName($definition));
            }

            // Convert
            $scalar                    = $this->getScalarDefinitionNode($type, $scalars[$type]);
            $scalar->directives        = $this->getScalarDirectives($scalar->directives, $definition->directives);
            $documentAST->types[$type] = $scalar;
        }

        // Apply `extend scalar`.
        foreach ($documentAST->typeExtensions as $type => $extensions) {
            // Supported?
            if (!array_key_exists($type, $scalars)) {
                continue;
            }

            // Scalar?
            $documentAST->types[$type] ??= $this->getScalarDefinitionNode($type, $scalars[$type]);

            if (!($documentAST->types[$type] instanceof ScalarTypeDefinitionNode)) {
                throw new TypeDefinitionIsNotScalar($type, $manipulator->getTypeFullName($documentAST->types[$type]));
            }

            // Extend
            foreach ($extensions as $key => $extension) {
                // Valid?
                if (!($extension instanceof ScalarTypeExtensionNode)) {
                    throw new TypeDefinitionIsNotScalarExtension($type, $this->getExtensionNodeName($extension));
                }

                // Directives
                $documentAST->types[$type]->directives = $this->getScalarDirectives(
                    $documentAST->types[$type]->directives,
                    $extension->directives,
                );

                // Remove to avoid conflicts with future Lighthouse version
                unset($documentAST->typeExtensions[$type][$key]);
            }

            // Remove to avoid conflicts with future Lighthouse version
            if (count($documentAST->typeExtensions[$type]) === 0) {
                unset($documentAST->typeExtensions[$type]);
            }
        }

        // Remove self
        unset($documentAST->types[$typeDefinition->getName()->value]);
    }

    /**
     * @return array<string, ?class-string<ScalarType>>
     */
    abstract protected function getScalars(AstManipulator $manipulator): array;

    /**
     * @param class-string<ScalarType>|null $class
     */
    protected function getScalarDefinition(string $name, string $class = null): string {
        $class ??= Internal::class;
        $value   = Cast::to(Node::class, AST::astFromValue($class, Type::string()));
        $value   = Printer::doPrint($value);
        $scalar  = "scalar {$name} @scalar(class: {$value})";

        return $scalar;
    }

    /**
     * @param class-string<ScalarType>|null $class
     */
    protected function getScalarDefinitionNode(string $name, string $class = null): ScalarTypeDefinitionNode {
        return Parser::scalarTypeDefinition($this->getScalarDefinition($name, $class));
    }

    /**
     * @param NodeList<DirectiveNode> $target
     * @param NodeList<DirectiveNode> $directives
     *
     * @return NodeList<DirectiveNode>
     */
    protected function getScalarDirectives(NodeList $target, NodeList $directives): NodeList {
        $scalarDirective = DirectiveLocator::directiveName(ScalarDirective::class);

        foreach ($directives as $directive) {
            if ($directive->name->value === $scalarDirective) {
                continue;
            }

            $target[] = $directive;
        }

        return $target;
    }

    private function getExtensionNodeName(TypeExtensionNode $node): string {
        $name = $node->getName()->value;
        $type = match (true) {
            $node instanceof EnumTypeExtensionNode        => 'enum',
            $node instanceof InputObjectTypeExtensionNode => 'input',
            $node instanceof InterfaceTypeExtensionNode   => 'interface',
            $node instanceof ObjectTypeExtensionNode      => 'object',
            $node instanceof ScalarTypeExtensionNode      => 'scalar',
            $node instanceof UnionTypeExtensionNode       => 'union',
            default                                       => 'unknown',
        };

        return "extend {$type} {$name}";
    }
}
