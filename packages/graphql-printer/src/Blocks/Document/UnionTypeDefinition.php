<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Type\Definition\UnionType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\UnionDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;
use Override;

/**
 * @internal
 *
 * @extends UnionDefinitionBlock<UnionTypeDefinitionNode|UnionType>
 */
#[GraphQLAstNode(UnionTypeDefinitionNode::class)]
#[GraphQLDefinition(UnionType::class)]
class UnionTypeDefinition extends UnionDefinitionBlock {
    public function __construct(
        Context $context,
        UnionTypeDefinitionNode|UnionType $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'union';
    }
}
