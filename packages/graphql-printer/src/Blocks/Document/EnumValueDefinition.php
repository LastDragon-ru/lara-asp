<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 * @extends DefinitionBlock<EnumValueDefinitionNode|GraphQLEnumValueDefinition>
 */
#[GraphQLAstNode(EnumValueDefinitionNode::class)]
#[GraphQLDefinition(GraphQLEnumValueDefinition::class)]
class EnumValueDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        EnumValueDefinitionNode|GraphQLEnumValueDefinition $value,
    ) {
        parent::__construct($context, $value);
    }
}
