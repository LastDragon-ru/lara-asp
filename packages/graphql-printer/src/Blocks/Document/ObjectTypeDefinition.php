<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\TypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 *
 * @extends TypeDefinitionBlock<ObjectTypeDefinitionNode|ObjectType>
 */
#[GraphQLAstNode(ObjectTypeDefinitionNode::class)]
#[GraphQLDefinition(ObjectType::class)]
class ObjectTypeDefinition extends TypeDefinitionBlock {
    public function __construct(
        Context $context,
        ObjectTypeDefinitionNode|ObjectType $definition,
    ) {
        parent::__construct($context, $definition);
    }

    protected function prefix(): ?string {
        return 'type';
    }
}
