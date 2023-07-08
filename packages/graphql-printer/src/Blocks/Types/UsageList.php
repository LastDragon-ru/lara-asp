<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function mb_strlen;

/**
 * @internal
 * @template TBlock of Block
 * @template TKey of array-key
 * @template TItem
 * @extends ListBlock<TBlock, TKey, TItem>
 */
abstract class UsageList extends ListBlock {
    /**
     * @inheritDoc
     */
    public function __construct(
        Context $context,
        iterable $items,
        protected bool $isAlwaysMultiline = false,
    ) {
        parent::__construct($context, $items);
    }

    public function isMultiline(int $level, int $used): bool {
        return parent::isMultiline($level, $used) || $this->isAlwaysMultiline();
    }

    protected function isAlwaysMultiline(): bool {
        return $this->isAlwaysMultiline;
    }

    abstract protected function separator(): string;

    abstract protected function prefix(): string;

    protected function getSeparator(): string {
        return "{$this->space()}{$this->separator()}{$this->space()}";
    }

    protected function getMultilineItemPrefix(): string {
        return "{$this->separator()}{$this->space()}";
    }

    protected function content(Collector $collector, int $level, int $used): string {
        $space   = $this->space();
        $prefix  = $this->prefix();
        $level   = $level + 1;
        $used    = $used + mb_strlen("{$prefix}{$space}");
        $content = parent::content($collector, $level, $used);

        if ($content) {
            if ($this->isAlwaysMultiline() || $this->isStringMultiline($content)) {
                $eol    = $this->eol();
                $indent = $this->indent($level);

                if ($prefix) {
                    $content = "{$prefix}{$eol}{$indent}{$content}";
                }
            } elseif ($prefix) {
                $content = "{$prefix}{$space}{$content}";
            } else {
                // empty
            }
        }

        return $content;
    }
}
