<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\AST\TypeExtensionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\AST;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeDefinitionInvalidExtension;
use LastDragon_ru\LaraASP\GraphQL\Builder\Scalars\TypeExtension;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\TypeDefinitionUnknown;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\TypeExtensionManipulator;
use Override;

use function array_key_exists;

/**
 * Merges the directive from type extension into target type.
 *
 * Lighthouse doesn't support adding directives from extensions nodes yet. It
 * may be useful for adding new operators, especially for scalars. So we are
 * providing this via {@see TypeExtensionManipulator}. In addition, the trait
 * also supports own scalars auto-registration so the user can just use
 * `extend scalar ...` without the proper scalar type (the `@scalar` directive
 * may be required to avoid broken schema because of how the Lighthouse convert
 * nodes into Types).
 *
 * @see https://github.com/nuwave/lighthouse/issues/2509
 * @see https://github.com/nuwave/lighthouse/pull/2512
 *
 * @phpstan-require-extends BaseDirective
 * @phpstan-require-implements TypeExtensionManipulator
 */
trait TypeExtender {
    #[Override]
    public function manipulateTypeExtension(DocumentAST &$documentAST, TypeExtensionNode &$typeExtension): void {
        // Node
        $name = $typeExtension->getName()->value;
        $node = $documentAST->types[$name] ?? null;

        // Create scalar if not exists
        if ($typeExtension instanceof ScalarTypeExtensionNode && $node === null) {
            $extendable = $this->getExtendableScalars();

            if (array_key_exists($name, $extendable)) {
                $class                     = $extendable[$name] ?? TypeExtension::class;
                $attr                      = Cast::to(Node::class, AST::astFromValue($class, Type::string()));
                $attr                      = Printer::doPrint($attr);
                $node                      = Parser::scalarTypeDefinition("scalar {$name} @scalar(class: {$attr})");
                $documentAST->types[$name] = $node;
            }
        }

        // Exists?
        if ($node === null) {
            throw new TypeDefinitionUnknown($name);
        }

        // Valid & Supported?
        $target = match (true) {
            $typeExtension instanceof EnumTypeExtensionNode        => EnumTypeDefinitionNode::class,
            $typeExtension instanceof ScalarTypeExtensionNode      => ScalarTypeDefinitionNode::class,
            $typeExtension instanceof ObjectTypeExtensionNode      => ObjectTypeDefinitionNode::class,
            $typeExtension instanceof InterfaceTypeExtensionNode   => InterfaceTypeDefinitionNode::class,
            $typeExtension instanceof InputObjectTypeExtensionNode => InputObjectTypeDefinitionNode::class,
            default                                                => null,
        };

        if ($target === null) {
            return;
        }

        if (!($node instanceof $target)) {
            throw new TypeDefinitionInvalidExtension(
                $name,
                $typeExtension instanceof Node
                    ? $typeExtension->kind
                    : $typeExtension::class,
            );
        }

        // Merge
        $node->directives = $node->directives->merge([
            $this->directiveNode,
        ]);
    }

    /**
     * @return array<string, ?class-string>
     */
    abstract protected function getExtendableScalars(): array;
}
