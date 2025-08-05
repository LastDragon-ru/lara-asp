<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Values;

use GraphQL\Language\AST\VariableNode;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 */
class VariableValue extends Block implements NamedBlock {
    public function __construct(
        Context $context,
        protected VariableNode $node,
    ) {
        parent::__construct($context);
    }

    #[Override]
    public function getName(): string {
        return $this->node->name->value;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        return "\${$this->getName()}";
    }
}
