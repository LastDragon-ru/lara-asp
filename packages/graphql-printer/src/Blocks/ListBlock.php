<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use ArrayAccess;
use Countable;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;

use function array_filter;
use function count;
use function mb_strlen;
use function strnatcmp;
use function usort;

/**
 * @internal
 * @template TBlock of Block
 * @implements ArrayAccess<string|int,TBlock>
 */
abstract class ListBlock extends Block implements Statistics, ArrayAccess, Countable {
    /**
     * @var array<int|string,TBlock>
     */
    private array $blocks = [];

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
    protected function getBlocks(int $level, int $used): array {
        $blocks = array_filter($this->blocks, static function (Block $block) use ($level, $used): bool {
            return $block->serialize($level, $used) !== '';
        });

        if (count($blocks) > 0 && $this->isNormalized()) {
            usort($blocks, static function (Block $a, Block $b): int {
                $aName = $a instanceof NamedBlock ? $a->getName() : '';
                $bName = $b instanceof NamedBlock ? $b->getName() : '';

                return strnatcmp($aName, $bName);
            });
        }

        return $blocks;
    }

    protected function content(int $level, int $used): string {
        // Blocks?
        $content = '';
        $blocks  = $this->getBlocks($level, $used);
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
                $block     = $this->analyze($block);
                $multiline = $wrapped && $block->isMultiline($level, $used);

                if (($multiline && $index > 0) || $previous) {
                    $content .= $eol;
                }

                if ($index > 0 || $isWrapped) {
                    $content .= $indent;
                }

                $content .= "{$separator}{$block->serialize($level + (int) $isWrapped, $used)}";

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
                $content .= "{$this->analyze($block)->serialize($level, $used)}".($index !== $last ? $separator : '');
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

        foreach ($blocks as $block) {
            $length += $block->getLength();

            if ($block->isMultiline($level, $used)) {
                $multiline = true;
                break;
            }
        }

        if ($multiline) {
            return true;
        }

        // Length?
        $count  = count($blocks);
        $length = $this->getUsed()
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
    protected function analyze(Block $block): Block {
        return $this->addUsed($block);
    }

    protected function reset(): void {
        foreach ($this->blocks as $block) {
            $block->reset();
        }

        parent::reset();
    }
    // </editor-fold>

    // <editor-fold desc="ArrayAccess">
    // =========================================================================
    /**
     * @param int|string $offset
     */
    public function offsetExists(mixed $offset): bool {
        return isset($this->blocks[$offset]);
    }

    /**
     * @param int|string $offset
     *
     * @return TBlock
     */
    public function offsetGet(mixed $offset): Block {
        return $this->blocks[$offset];
    }

    /**
     * @param int|string|null $offset
     * @param TBlock          $value
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        if ($offset !== null) {
            $this->blocks[$offset] = $value;
        } else {
            $this->blocks[] = $value;
        }

        parent::reset();
    }

    /**
     * @param int|string $offset
     */
    public function offsetUnset(mixed $offset): void {
        unset($this->blocks[$offset]);

        parent::reset();
    }
    // </editor-fold>

    // <editor-fold desc="Countable">
    // =========================================================================
    public function count(): int {
        return count($this->blocks);
    }
    // </editor-fold>
}
