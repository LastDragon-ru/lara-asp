<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeExtensionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;
use GraphQL\Language\DirectiveLocation;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\AST;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionIsNotScalarExtension;
use LastDragon_ru\LaraASP\GraphQL\Builder\ManipulatorFactory;
use LastDragon_ru\LaraASP\GraphQL\Builder\Scalars\Internal;
use Nuwave\Lighthouse\Events\BuildSchemaString;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\TypeManipulator;
use Override;

use function count;
use function implode;
use function in_array;
use function str_starts_with;

/**
 * Modifies the Schema for Directive.
 *
 * We are using special scalars to add operators. The directive provides a way
 * to add and extend them. Extending is required because Lighthouse (until v6.34.0)
 * doesn't support adding directives from extensions nodes yet.
 *
 * @see https://github.com/nuwave/lighthouse/issues/2509
 * @see https://github.com/nuwave/lighthouse/pull/2512
 *
 * @internal
 */
abstract class SchemaDirective extends BaseDirective implements TypeManipulator {
    public function __construct(
        private readonly ManipulatorFactory $manipulatorFactory,
    ) {
        // empty
    }

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
        // todo(graphql): Lighthouse since v6.34.0 merges directives from
        //      extension nodes except standard types. So the implementation can
        //      be simplified.

        // Apply `extend scalar`.
        $manipulator = $this->manipulatorFactory->create($documentAST);

        foreach ($documentAST->typeExtensions as $type => $extensions) {
            // Supported?
            // (no way to extend standard types, we are trying to use alias instead)
            $targetType = ($manipulator->isStandard($type) ? $this->getScalar() : '').$type;

            if (!$this->isScalar($targetType)) {
                continue;
            }

            // Extend
            $targetNode = $manipulator->addTypeDefinition($this->getScalarDefinitionNode($targetType));

            foreach ($extensions as $key => $extension) {
                // Valid?
                if (!($extension instanceof ScalarTypeExtensionNode)) {
                    throw new TypeDefinitionIsNotScalarExtension($targetType, $this->getExtensionNodeName($extension));
                }

                // Directives
                if ($targetType === $type) {
                    $targetNode->directives = $targetNode->directives->merge($extension->directives);
                } else {
                    // Only known directives will be copied for alias
                    foreach ($extension->directives as $index => $directive) {
                        if ($this->isDirective($directive->name->value)) {
                            $targetNode->directives[] = $directive;

                            unset($extension->directives[$index]);
                        }
                    }

                    $extension->directives->reindex();
                }

                // Remove to avoid conflicts with future Lighthouse version
                unset($documentAST->typeExtensions[$type][$key]);

                if (count($documentAST->typeExtensions[$type]) === 0) {
                    unset($documentAST->typeExtensions[$type]);
                }
            }
        }

        // Remove self
        $manipulator->removeTypeDefinition($typeDefinition->getName()->value);
    }

    protected function isDirective(string $name): bool {
        return str_starts_with($name, $this->getDirective());
    }

    abstract protected function getDirective(): string;

    abstract protected function getScalar(): string;

    /**
     * @return array<array-key, string>
     */
    abstract protected function getScalars(): array;

    protected function isScalar(string $name): bool {
        return $name !== $this->getScalar()
            && str_starts_with($name, $this->getScalar())
            && in_array($name, $this->getScalars(), true);
    }

    protected function getScalarDefinition(string $name): string {
        $class  = Internal::class;
        $value  = Cast::to(Node::class, AST::astFromValue($class, Type::string()));
        $value  = Printer::doPrint($value);
        $scalar = <<<GRAPHQL
            """
            The scalar is used to add builder operators for `@{$this->getDirective()}` directive.
            """
            scalar {$name}
            @scalar(
                class: {$value}
            )
            GRAPHQL;

        return $scalar;
    }

    protected function getScalarDefinitionNode(string $name): ScalarTypeDefinitionNode {
        return Parser::scalarTypeDefinition($this->getScalarDefinition($name));
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
