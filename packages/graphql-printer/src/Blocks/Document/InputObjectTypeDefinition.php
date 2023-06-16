<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Type\Definition\InputObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\InputObjectDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 *
 * @extends InputObjectDefinitionBlock<InputObjectTypeDefinitionNode|InputObjectType>
 */
#[GraphQLAstNode(InputObjectTypeDefinitionNode::class)]
#[GraphQLDefinition(InputObjectType::class)]
class InputObjectTypeDefinition extends InputObjectDefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        InputObjectTypeDefinitionNode|InputObjectType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'input';
    }
}
