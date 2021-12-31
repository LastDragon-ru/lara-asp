<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

use ArrayAccess;
use LastDragon_ru\LaraASP\GraphQL\Printer\Settings;
use function count;
use function implode;
use function ksort;
use function mb_strlen;
use const SORT_NATURAL;

/**
 * @internal
 * @implements ArrayAccess<string,Block>
 */
class BlockList extends Block implements ArrayAccess {
    /**
     * @var array<string,Block>
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
    ) {
        parent::__construct($settings, $level, $used);
    }

    protected function isMultiline(): bool {
        return count($this->multiline) > 0 || parent::isMultiline();
    }

    protected function isNormalized(): bool {
        return $this->normalized;
    }

    protected function isWrapped(): bool {
        return $this->wrapped;
    }

    protected function serialize(): string {
        // Blocks?
        $blocks = $this->blocks;
        $count  = count($blocks);

        if ($this->isNormalized()) {
            ksort($blocks, SORT_NATURAL);
        }

        if (!$count) {
            return '';
        }

        // Join
        $separator   = ",{$this->space()}";
        $isMultiline = count($this->multiline) > 0 || $this->isLineTooLong(
            $this->used + $this->length + mb_strlen($separator) * ($count - 1),
        );
        $content     = '';

        if ($isMultiline) {
            $eol      = $this->eol();
            $last     = $count - 1;
            $index    = 0;
            $indent   = $this->indent();
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
            $content = implode($separator, $blocks);
        }

        // Return
        return $content;
    }

    // <editor-fold desc="\ArrayAccess">
    // =========================================================================
    /**
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool {
        return isset($this->blocks[$offset]);
    }

    /**
     * @param string $offset
     */
    public function offsetGet(mixed $offset): Block {
        return $this->blocks[$offset];
    }

    /**
     * @param string $offset
     * @param Block  $value
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        $this->blocks[$offset] = $value;
        $this->length         += $value->getLength();

        if ($value->isMultiline()) {
            $this->multiline[$offset] = true;
        }

        $this->reset();
    }

    /**
     * @param string $offset
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
