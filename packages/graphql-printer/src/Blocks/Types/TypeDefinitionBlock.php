<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\FieldsDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\ImplementsInterfaces;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function mb_strlen;

// @phpcs:disable Generic.Files.LineLength.TooLong

/**
 * @internal
 *
 * @template TType of InterfaceTypeDefinitionNode|InterfaceTypeExtensionNode|ObjectTypeDefinitionNode|ObjectTypeExtensionNode|InterfaceType|ObjectType
 *
 * @extends DefinitionBlock<TType>
 */
abstract class TypeDefinitionBlock extends DefinitionBlock {
    /**
     * @param TType $definition
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        InterfaceTypeDefinitionNode|InterfaceTypeExtensionNode|ObjectTypeDefinitionNode|ObjectTypeExtensionNode|InterfaceType|ObjectType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function body(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $interfaces = $this->addUsed(
            new ImplementsInterfaces(
                $this->getContext(),
                $this->getLevel() + 1,
                $used + mb_strlen($space),
                $definition instanceof InterfaceType || $definition instanceof ObjectType
                    ? $definition->getInterfaces()
                    : $definition->interfaces,
            ),
        );

        if (!$interfaces->isEmpty()) {
            if ($interfaces->isMultiline()) {
                $eol        = $this->eol();
                $indent     = $this->indent($this->getLevel());
                $interfaces = "{$eol}{$indent}{$interfaces}";
            } else {
                $interfaces = "{$space}{$interfaces}";
            }
        }

        return $interfaces;
    }

    protected function fields(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $fields     = new FieldsDefinition(
            $this->getContext(),
            $this->getLevel(),
            $used + mb_strlen($space),
            $definition instanceof InterfaceType || $definition instanceof ObjectType
                ? $definition->getFields()
                : $definition->fields,
        );

        return $this->addUsed($fields);
    }
}
