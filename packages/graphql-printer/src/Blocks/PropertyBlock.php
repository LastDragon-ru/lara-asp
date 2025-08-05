<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks;

use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use Override;

use function mb_strlen;

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

    #[Override]
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

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        $prefix  = "{$this->getName()}{$this->getSeparator()}{$this->space()}";
        $block   = $this->getBlock()->serialize($collector, $level, $used + mb_strlen($prefix));
        $content = "{$prefix}{$block}";

        return $content;
    }
}
