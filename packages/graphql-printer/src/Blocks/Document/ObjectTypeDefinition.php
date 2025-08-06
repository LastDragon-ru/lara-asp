<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ObjectDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLAstNode;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLDefinition;
use Override;

/**
 * @internal
 *
 * @extends ObjectDefinitionBlock<ObjectTypeDefinitionNode|ObjectType>
 */
#[GraphQLAstNode(ObjectTypeDefinitionNode::class)]
#[GraphQLDefinition(ObjectType::class)]
class ObjectTypeDefinition extends ObjectDefinitionBlock {
    public function __construct(
        Context $context,
        ObjectTypeDefinitionNode|ObjectType $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'type';
    }
}
