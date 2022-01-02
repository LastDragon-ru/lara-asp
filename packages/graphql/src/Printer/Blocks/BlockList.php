<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

use ArrayAccess;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;

use function count;
use function implode;
use function is_numeric;
use function ksort;
use function mb_strlen;

use const SORT_NATURAL;

/**
 * @internal
 * @implements ArrayAccess<string,Block>
 */
class BlockList extends Block implements ArrayAccess {
    /**
     * @var array<int|string,Block>
     */
    private array $blocks = [];

    /**
     * @var array<string,bool>
     */
    private array $multiline = [];
    private int   $length    = 0;

    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        private bool $normalized = false,
        private bool $wrapped = false,
        private string $prefix = '',
        private string $suffix = '',
        private string $separator = ',',
    ) {
        parent::__construct($settings, $level, $used);
    }

    protected function isNormalized(): bool {
        return $this->normalized;
    }

    protected function isWrapped(): bool {
        return $this->wrapped;
    }

    public function getPrefix(): string {
        return $this->prefix;
    }

    public function getSuffix(): string {
        return $this->suffix;
    }

    public function getSeparator(): string {
        return $this->separator;
    }

    public function isMultiline(): bool {
        return count($this->multiline) > 0 || parent::isMultiline();
    }

    /**
     * @return array<int|string,Block>
     */
    protected function getBlocks(): array {
        $blocks = $this->blocks;

        if ($this->isNormalized()) {
            ksort($blocks, SORT_NATURAL);
        }

        return $blocks;
    }

    protected function content(): string {
        // Blocks?
        $content = '';
        $blocks  = $this->getBlocks();
        $count   = count($blocks);

        if (!$count) {
            return $content;
        }

        // Join
        $eol           = '';
        $listPrefix    = $this->getPrefix();
        $listSuffix    = $this->getSuffix();
        $itemSeparator = "{$this->getSeparator()}{$this->space()}";
        $isMultiline   = $this->isMultilineContent(
            $blocks,
            $listSuffix,
            $listPrefix,
            $itemSeparator,
        );

        if ($isMultiline) {
            $eol      = $this->eol();
            $last     = $count - 1;
            $index    = 0;
            $indent   = $this->indent($this->getLevel() + (int) ($listPrefix || $listSuffix));
            $wrapped  = $this->isWrapped();
            $previous = false;

            foreach ($blocks as $block) {
                $multiline = $wrapped && $block->isMultiline();

                if (($multiline && $index > 0) || $previous) {
                    $content .= $eol;
                }

                $content .= "{$indent}{$block}";

                if ($index < $last) {
                    $content .= $eol;
                }

                $previous = $multiline;
                $index    = $index + 1;
            }
        } else {
            $content = implode($itemSeparator, $blocks);
        }

        // Prefix & Suffix
        if ($listPrefix || $listSuffix) {
            $indent  = $isMultiline ? $this->indent() : '';
            $content = "{$listPrefix}{$eol}{$content}{$eol}{$indent}{$listSuffix}";
        }

        // Return
        return $content;
    }

    /**
     * @param array<int|string,Block> $blocks
     */
    protected function isMultilineContent(
        array $blocks,
        string $suffix,
        string $prefix,
        string $itemSeparator,
    ): bool {
        // Any multiline block?
        if ($this->multiline) {
            return true;
        }

        // Length?
        $count  = count($blocks);
        $length = $this->getUsed()
            + $this->length
            + mb_strlen($suffix)
            + mb_strlen($prefix)
            + mb_strlen($itemSeparator) * ($count - 1);

        return $this->isLineTooLong($length);
    }

    // <editor-fold desc="\ArrayAccess">
    // =========================================================================
    /**
     * @param int|string $offset
     */
    public function offsetExists(mixed $offset): bool {
        return isset($this->blocks[$offset]);
    }

    /**
     * @param int|string $offset
     */
    public function offsetGet(mixed $offset): Block {
        return $this->blocks[$offset];
    }

    /**
     * @param int|string|null $offset
     * @param Block           $value
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        if ($offset !== null) {
            if (!is_numeric($offset)) {
                $value = new NamedBlock($this->getSettings(), $offset, $value);
            }

            $this->blocks[$offset] = $value;
        } else {
            $this->blocks[] = $value;
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
}
