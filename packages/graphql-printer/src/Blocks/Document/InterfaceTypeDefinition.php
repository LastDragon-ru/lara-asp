<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Type\Definition\InterfaceType;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ObjectDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLDefinition;
use Override;

/**
 * @internal
 *
 * @extends ObjectDefinitionBlock<InterfaceTypeDefinitionNode|InterfaceType>
 */
#[GraphQLAstNode(InterfaceTypeDefinitionNode::class)]
#[GraphQLDefinition(InterfaceType::class)]
class InterfaceTypeDefinition extends ObjectDefinitionBlock {
    public function __construct(
        Context $context,
        InterfaceTypeDefinitionNode|InterfaceType $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'interface';
    }
}
