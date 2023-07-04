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

    protected function body(int $level, int $used, bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $implements = new ImplementsInterfaces(
            $this->getContext(),
            $level + 1,
            $used,
            $definition instanceof InterfaceType || $definition instanceof ObjectType
                ? $definition->getInterfaces()
                : $definition->interfaces,
        );

        return $implements;
    }

    protected function fields(int $level, int $used, bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $fields     = new FieldsDefinition(
            $this->getContext(),
            $level,
            $used,
            $definition instanceof InterfaceType || $definition instanceof ObjectType
                ? $definition->getFields()
                : $definition->fields,
        );

        return $fields;
    }
}
