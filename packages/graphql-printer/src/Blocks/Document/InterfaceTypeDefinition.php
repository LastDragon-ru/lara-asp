<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Type\Definition\InterfaceType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\TypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 *
 * @extends TypeDefinitionBlock<InterfaceTypeDefinitionNode|InterfaceType>
 */
#[GraphQLAstNode(InterfaceTypeDefinitionNode::class)]
#[GraphQLDefinition(InterfaceType::class)]
class InterfaceTypeDefinition extends TypeDefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        InterfaceTypeDefinitionNode|InterfaceType $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'interface';
    }
}
