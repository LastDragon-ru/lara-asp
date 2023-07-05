<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values;

use GraphQL\Language\AST\VariableNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

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

    public function getName(): string {
        return $this->node->name->value;
    }

    protected function content(int $level, int $used): string {
        return "\${$this->getName()}";
    }
}
