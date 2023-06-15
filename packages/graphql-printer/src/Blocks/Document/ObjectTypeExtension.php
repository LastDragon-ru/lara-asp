<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ObjectTypeExtensionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\TypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 *
 * @extends TypeDefinitionBlock<ObjectTypeExtensionNode>
 */
#[GraphQLAstNode(ObjectTypeExtensionNode::class)]
class ObjectTypeExtension extends TypeDefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        ObjectTypeExtensionNode $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'extend type';
    }
}
