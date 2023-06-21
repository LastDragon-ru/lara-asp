<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Values;

use GraphQL\Language\AST\ListValueNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document\Value;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 * @extends ListBlock<Value>
 */
class ListValue extends ListBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        ListValueNode $definition,
    ) {
        parent::__construct($context, $level, $used);

        foreach ($definition->values as $value) {
            $this[] = new Value($context, $level + 1, $used, $value);
        }
    }

    protected function getPrefix(): string {
        return '[';
    }

    protected function getSuffix(): string {
        return ']';
    }

    protected function getEmptyValue(): string {
        return "{$this->getPrefix()}{$this->getSuffix()}";
    }
}
