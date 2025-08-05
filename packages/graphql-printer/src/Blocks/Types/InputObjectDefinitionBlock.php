<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Types;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Type\Definition\InputObjectType;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\Document\InputFieldsDefinition;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 *
 * @template TType of InputObjectTypeDefinitionNode|InputObjectTypeExtensionNode|InputObjectType
 *
 * @extends DefinitionBlock<TType>
 */
abstract class InputObjectDefinitionBlock extends DefinitionBlock implements TypeDefinitionBlock {
    public function __construct(
        Context $context,
        InputObjectTypeDefinitionNode|InputObjectTypeExtensionNode|InputObjectType $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function fields(bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $fields     = new InputFieldsDefinition(
            $this->getContext(),
            $definition instanceof InputObjectType
                ? $definition->getFields()
                : $definition->fields,
        );

        return $fields;
    }
}
