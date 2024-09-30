<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Override;

use function count;
use function mb_strlen;
use function strnatcmp;
use function usort;

/**
 * @internal
 * @template TBlock of Block
 * @template TKey of string|int
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
     * @return list<TBlock>
     */
    private function getBlocks(): array {
        // Create
        $blocks = [];

        foreach ($this->getItems() as $key => $item) {
            $blocks[] = $this->block($key, $item);
        }

        // Sort
        if (count($blocks) > 0 && $this->isNormalized()) {
            usort($blocks, static function (Block $a, Block $b): int {
                if ($a instanceof NamedBlock && $b instanceof NamedBlock) {
                    return strnatcmp($a->getName(), $b->getName());
                } elseif ($a instanceof NamedBlock) {
                    return -1;
                } elseif ($b instanceof NamedBlock) {
                    return 1;
                } else {
                    // empty
                }

                return 0;
            });
        }

        return $blocks;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        // Serialize
        /** @var array<int, array{bool, non-empty-string}> $serialized */
        $serialized      = [];
        $multiline       = $this->isAlwaysMultiline();
        $prefix          = $this->getPrefix();
        $prefixLength    = mb_strlen($prefix);
        $suffix          = $this->getSuffix();
        $suffixLength    = mb_strlen($suffix);
        $isWrapped       = ((bool) $prefix || (bool) $suffix);
        $blocks          = $this->getBlocks();
        $blockLevel      = $level + (int) $isWrapped;
        $blockIndent     = $this->indent($blockLevel);
        $blockPrefix     = $this->getMultilineItemPrefix();
        $lineUsed        = mb_strlen($blockIndent) + mb_strlen($blockPrefix) + $suffixLength;
        $lineLength      = $used + $prefixLength;
        $separator       = $this->getSeparator();
        $separatorLength = mb_strlen($separator);

        if ($isWrapped && $multiline) {
            $used = $lineUsed;
        }

        foreach ($blocks as $block) {
            // Serialize
            $blockContent   = $block->serialize($collector, $blockLevel, $used);
            $blockMultiline = $this->isStringMultiline($blockContent);

            if ($blockContent === '') {
                continue;
            }

            // Single line?
            if (!$multiline) {
                $lineLength += mb_strlen($blockContent) + $separatorLength * (count($serialized) + 1);

                if ($blockMultiline || $this->isLineTooLong($lineLength + $suffixLength)) {
                    $used           = $lineUsed;
                    $multiline      = true;
                    $blockContent   = $block->serialize($collector, $blockLevel, $used);
                    $blockMultiline = $this->isStringMultiline($blockContent);
                } else {
                    $used = $lineLength;
                }
            } else {
                $used = $lineUsed;
            }

            // Add
            $serialized[] = [$blockMultiline, $blockContent];
        }

        // Empty?
        if ($serialized === []) {
            return $this->getEmptyValue();
        }

        // Join
        $eol                = $this->eol();
        $content            = '';
        $multiline          = $multiline || $this->isLineTooLong($lineLength + mb_strlen($suffix));
        $separator          = $multiline ? $eol : $this->getSeparator();
        $isMultilineWrapped = $multiline && $this->isWrapped();

        for ($index = 0, $count = count($serialized); $index < $count; $index++) {
            [$blockMultiline, $blockContent] = $serialized[$index];
            $previousMultiline               = $isMultilineWrapped && ($serialized[$index - 1][0] ?? false);
            $isLast                          = ($index === $count - 1);

            if ($multiline) {
                if (($blockMultiline && $isMultilineWrapped && $index > 0) || $previousMultiline) {
                    $content .= $eol;
                }

                if ($index > 0 || $isWrapped) {
                    $content .= $blockIndent;
                }

                $content .= $blockPrefix.$blockContent;

                if (!$isLast) {
                    $content .= $eol;
                }
            } else {
                $content .= $blockContent.($isLast ? '' : $separator);
            }
        }

        // Prefix & Suffix
        if ($prefix !== '') {
            $prefix  = $multiline ? $prefix.$eol : $prefix;
            $content = "{$prefix}{$content}";
        }

        if ($suffix !== '') {
            $indent   = $multiline ? $eol.$this->indent($level) : '';
            $content .= "{$indent}{$suffix}";
        }

        // Return
        return $content;
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
