<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Type\Definition\EnumType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\EnumValuesDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function mb_strlen;

/**
 * @internal
 *
 * @template TType of EnumTypeDefinitionNode|EnumTypeExtensionNode|EnumType
 *
 * @extends DefinitionBlock<TType>
 */
abstract class EnumDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        EnumTypeDefinitionNode|EnumTypeExtensionNode|EnumType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function body(int $used): Block|string|null {
        return null;
    }

    protected function fields(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $values     = $this->addUsed(
            new EnumValuesDefinition(
                $this->getContext(),
                $this->getLevel(),
                $used + mb_strlen($space),
                $definition instanceof EnumType
                    ? $definition->getValues()
                    : $definition->values,
            ),
        );

        return $values;
    }
}
