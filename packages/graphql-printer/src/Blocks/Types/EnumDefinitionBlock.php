<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Type\Definition\EnumType;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\EnumValuesDefinition;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 *
 * @template TType of EnumTypeDefinitionNode|EnumTypeExtensionNode|EnumType
 *
 * @extends DefinitionBlock<TType>
 */
abstract class EnumDefinitionBlock extends DefinitionBlock implements TypeDefinitionBlock {
    public function __construct(
        Context $context,
        EnumTypeDefinitionNode|EnumTypeExtensionNode|EnumType $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function fields(bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $values     = new EnumValuesDefinition(
            $this->getContext(),
            $definition instanceof EnumType
                ? $definition->getValues()
                : $definition->values,
        );

        return $values;
    }
}
