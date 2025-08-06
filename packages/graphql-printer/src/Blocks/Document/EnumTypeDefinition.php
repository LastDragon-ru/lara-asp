<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\PhpEnumType;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\EnumDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLAstNode;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLDefinition;
use Override;

/**
 * @internal
 *
 * @extends EnumDefinitionBlock<EnumTypeDefinitionNode|EnumType>
 */
#[GraphQLAstNode(EnumTypeDefinitionNode::class)]
#[GraphQLDefinition(EnumType::class)]
#[GraphQLDefinition(PhpEnumType::class)]
class EnumTypeDefinition extends EnumDefinitionBlock {
    public function __construct(
        Context $context,
        EnumTypeDefinitionNode|EnumType $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): string {
        return 'enum';
    }
}
