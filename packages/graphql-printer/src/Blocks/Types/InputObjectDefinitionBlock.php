<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Type\Definition\InputObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\InputFieldsDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 *
 * @template TType of InputObjectTypeDefinitionNode|InputObjectTypeExtensionNode|InputObjectType
 *
 * @extends DefinitionBlock<TType>
 */
abstract class InputObjectDefinitionBlock extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        InputObjectTypeDefinitionNode|InputObjectTypeExtensionNode|InputObjectType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function fields(int $used, bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $fields     = new InputFieldsDefinition(
            $this->getContext(),
            $this->getLevel(),
            $used,
            $definition instanceof InputObjectType
                ? $definition->getFields()
                : $definition->fields,
        );

        return $fields;
    }
}
