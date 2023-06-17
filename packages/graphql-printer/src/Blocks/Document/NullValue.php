<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NullValueNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 */
#[GraphQLAstNode(NullValueNode::class)]
class NullValue extends Block {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        protected NullValueNode $node,
    ) {
        parent::__construct($context, $level, $used);
    }

    protected function content(): string {
        return 'null';
    }
}
