<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function count;
use function mb_strlen;
use function strnatcmp;
use function usort;

/**
 * @internal
 * @template TBlock of Block
 * @template TKey of array-key
 * @template TItem
 */
abstract class ListBlock extends Block {
    /**
     * @param iterable<TKey, TItem> $items
     */
    public function __construct(
        Context $context,
        private iterable $items,
    ) {
        parent::__construct($context);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    /**
     * @return iterable<TKey, TItem>
     */
    protected function getItems(): iterable {
        return $this->items;
    }
    // </editor-fold>

    // <editor-fold desc="Settings">
    // =========================================================================
    protected function isWrapped(): bool {
        return false;
    }

    protected function isNormalized(): bool {
        return false;
    }

    protected function isAlwaysMultiline(): bool {
        return false;
    }

    protected function getPrefix(): string {
        return '';
    }

    protected function getSuffix(): string {
        return '';
    }

    protected function getSeparator(): string {
        return ",{$this->space()}";
    }

    protected function getMultilineItemPrefix(): string {
        return '';
    }

    protected function getEmptyValue(): string {
        return '';
    }
    // </editor-fold>

    // <editor-fold desc="Content">
    // =========================================================================
    /**
     * @return array<int|string,TBlock>
     */
    protected function getBlocks(Collector $collector, int $level, int $used): array {
        // Create
        $blocks = [];

        foreach ($this->getItems() as $key => $item) {
            $block = $this->block($key, $item);

            if ($block->serialize($collector, $level, $used) !== '') {
                $blocks[] = $block;
            }
        }

        // Sort
        if (count($blocks) > 0 && $this->isNormalized()) {
            usort($blocks, static function (Block $a, Block $b): int {
                $aName = $a instanceof NamedBlock ? $a->getName() : '';
                $bName = $b instanceof NamedBlock ? $b->getName() : '';

                return strnatcmp($aName, $bName);
            });
        }

        return $blocks;
    }

    protected function content(Collector $collector, int $level, int $used): string {
        // Blocks?
        $content = '';
        $blocks  = $this->getBlocks($collector, $level, $used);
        $count   = count($blocks);

        if (!$count) {
            return $this->getEmptyValue();
        }

        // Join
        $listPrefix  = $this->getPrefix();
        $listSuffix  = $this->getSuffix();
        $separator   = $this->getSeparator();
        $isWrapped   = (bool) $listPrefix || (bool) $listSuffix;
        $isMultiline = $this->isMultilineContent(
            $level,
            $used,
            $blocks,
            $listSuffix,
            $listPrefix,
            $separator,
        );

        if ($isMultiline) {
            $eol       = $this->eol();
            $last      = $count - 1;
            $index     = 0;
            $indent    = $this->indent($level + (int) $isWrapped);
            $wrapped   = $this->isWrapped();
            $previous  = false;
            $separator = $this->getMultilineItemPrefix();

            foreach ($blocks as $block) {
                $block      = $this->analyze($collector, $block);
                $serialized = $block->serialize($collector, $level + (int) $isWrapped, $used);
                $multiline  = $wrapped && $block->isStringMultiline($serialized);

                if (($multiline && $index > 0) || $previous) {
                    $content .= $eol;
                }

                if ($index > 0 || $isWrapped) {
                    $content .= $indent;
                }

                $content .= "{$separator}{$serialized}";

                if ($index < $last) {
                    $content .= $eol;
                }

                $previous = $multiline;
                $index    = $index + 1;
            }
        } else {
            $last  = $count - 1;
            $index = 0;

            foreach ($blocks as $block) {
                $content .= "{$this->analyze($collector, $block)->serialize($collector, $level, $used)}";
                $content .= ($index !== $last ? $separator : '');
                $index    = $index + 1;
            }
        }

        // Prefix & Suffix
        if ($isWrapped) {
            $eol     = $isMultiline ? $this->eol() : '';
            $indent  = $isMultiline ? $this->indent($level) : '';
            $content = "{$listPrefix}{$eol}{$content}";

            if ($listSuffix) {
                $content .= "{$eol}{$indent}{$listSuffix}";
            }
        }

        // Return
        return $content;
    }

    /**
     * @param array<int|string,TBlock> $blocks
     */
    private function isMultilineContent(
        int $level,
        int $used,
        array $blocks,
        string $suffix,
        string $prefix,
        string $separator,
    ): bool {
        // Always or Any multiline block?
        if ($this->isAlwaysMultiline()) {
            return true;
        }

        // Any multiline block?
        $length    = 0;
        $multiline = false;
        $collector = new Collector();

        foreach ($blocks as $block) {
            $serialized = $block->serialize($collector, $level, $used);
            $length    += mb_strlen($serialized);

            if ($block->isStringMultiline($serialized)) {
                $multiline = true;
                break;
            }
        }

        if ($multiline) {
            return true;
        }

        // Length?
        $count  = count($blocks);
        $length = $used
            + $length
            + mb_strlen($suffix)
            + mb_strlen($prefix)
            + mb_strlen($separator) * ($count - 1);

        return $this->isLineTooLong($length);
    }

    /**
     * @param TBlock $block
     *
     * @return TBlock
     */
    protected function analyze(Collector $collector, Block $block): Block {
        return $collector->addUsed($block);
    }

    /**
     * @param TKey  $key
     * @param TItem $item
     *
     * @return TBlock
     */
    abstract protected function block(string|int $key, mixed $item): Block;
    // </editor-fold>
}
