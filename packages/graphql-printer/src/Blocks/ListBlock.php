<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use ArrayAccess;
use Countable;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Statistics;

use function array_key_last;
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

    /**
     * @var array<int|string,bool>
     */
    private array $multiline = [];
    private int   $length    = 0;

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

    // <editor-fold desc="Block">
    // =========================================================================
    public function isEmpty(): bool {
        return count($this->blocks) === 0 || parent::isEmpty();
    }

    public function isMultiline(): bool {
        return count($this->multiline) > 0 || parent::isMultiline();
    }
    // </editor-fold>

    // <editor-fold desc="Content">
    // =========================================================================
    /**
     * @return array<int|string,TBlock>
     */
    protected function getBlocks(): array {
        $blocks = $this->blocks;

        if (count($blocks) > 0 && $this->isNormalized()) {
            usort($blocks, static function (Block $a, Block $b): int {
                $aName = $a instanceof NamedBlock ? $a->getName() : '';
                $bName = $b instanceof NamedBlock ? $b->getName() : '';

                return strnatcmp($aName, $bName);
            });
        }

        return $blocks;
    }

    protected function content(): string {
        // Blocks?
        $content = '';
        $blocks  = $this->getBlocks();
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
            $blocks,
            $listSuffix,
            $listPrefix,
            $separator,
        );

        if ($isMultiline) {
            $eol       = $this->eol();
            $last      = $count - 1;
            $index     = 0;
            $indent    = $this->indent($this->getLevel() + (int) $isWrapped);
            $wrapped   = $this->isWrapped();
            $previous  = false;
            $separator = $this->getMultilineItemPrefix();

            foreach ($blocks as $block) {
                $block     = $this->analyze($block);
                $multiline = $wrapped && $block->isMultiline();

                if (($multiline && $index > 0) || $previous) {
                    $content .= $eol;
                }

                if ($index > 0 || $isWrapped) {
                    $content .= $indent;
                }

                $content .= "{$separator}{$block}";

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
                $content .= "{$this->analyze($block)}".($index !== $last ? $separator : '');
                $index    = $index + 1;
            }
        }

        // Prefix & Suffix
        if ($isWrapped) {
            $eol     = $isMultiline ? $this->eol() : '';
            $indent  = $isMultiline ? $this->indent() : '';
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
        array $blocks,
        string $suffix,
        string $prefix,
        string $separator,
    ): bool {
        // Always or Any multiline block?
        if ($this->isAlwaysMultiline() || count($this->multiline) > 0) {
            return true;
        }

        // Length?
        $count  = count($blocks);
        $length = $this->getUsed()
            + $this->length
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
        if ($value->isEmpty()) {
            return;
        }

        if ($offset !== null) {
            $this->blocks[$offset] = $value;
        } else {
            $this->blocks[] = $value;
            $offset         = array_key_last($this->blocks);
        }

        $this->length += $value->getLength();

        if ($value->isMultiline()) {
            $this->multiline[$offset] = true;
        }

        $this->reset();
    }

    /**
     * @param int|string $offset
     */
    public function offsetUnset(mixed $offset): void {
        if (isset($this->blocks[$offset])) {
            $this->length -= $this->blocks[$offset]->getLength();
        }

        unset($this->blocks[$offset]);
        unset($this->multiline[$offset]);

        $this->reset();
    }
    // </editor-fold>

    // <editor-fold desc="Countable">
    // =========================================================================
    public function count(): int {
        return count($this->blocks);
    }
    // </editor-fold>
}
