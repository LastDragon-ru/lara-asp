<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 *
 * @template TBlock of Block
 */
class PropertyBlock extends Block implements NamedBlock {
    /**
     * @param TBlock $block
     */
    public function __construct(
        Context $context,
        private string $name,
        private Block $block,
    ) {
        parent::__construct($context);
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @return TBlock
     */
    protected function getBlock(): Block {
        return $this->block;
    }

    protected function getSeparator(): string {
        return ':';
    }

    protected function content(Collector $collector, int $level, int $used): string {
        $block   = $this->getBlock()->serialize($collector, $level, $used);
        $content = "{$this->getName()}{$this->getSeparator()}{$this->space()}{$block}";

        return $content;
    }
}
