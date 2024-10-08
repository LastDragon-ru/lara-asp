<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer;

use ArrayAccess;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 * @extends ListBlock<Block, array-key, Block>
 * @implements ArrayAccess<Block, Block>
 */
class PrintableList extends ListBlock implements ArrayAccess {
    /**
     * @var array<array-key, Block>
     */
    private array $blocks = [];

    public function __construct(
        Context $context,
        protected bool $root = false,
        protected bool $eof = true,
    ) {
        parent::__construct($context, []);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    protected function isRoot(): bool {
        return $this->root;
    }
    // </editor-fold>

    // <editor-fold desc="Settings">
    // =========================================================================
    #[Override]
    protected function isWrapped(): bool {
        return true;
    }

    #[Override]
    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeDefinitions();
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return true;
    }
    // </editor-fold>

    // <editor-fold desc="Content">
    // =========================================================================
    /**
     * @inheritDoc
     */
    #[Override]
    protected function getItems(): iterable {
        return $this->blocks;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        $content = parent::content($collector, $level, $used);

        if ($content !== '' && $this->isRoot()) {
            $content = "{$this->indent($level)}{$content}";

            if ($this->eof) {
                $content .= $this->getSettings()->getFileEnd();
            }
        }

        return $content;
    }

    #[Override]
    protected function block(int|string $key, mixed $item): Block {
        return $item;
    }
    // </editor-fold>

    // <editor-fold desc="ArrayAccess">
    // =========================================================================
    /**
     * @param Block $offset
     */
    #[Override]
    public function offsetExists(mixed $offset): bool {
        return isset($this->blocks[$this->offset($offset)]);
    }

    /**
     * @param Block $offset
     */
    #[Override]
    public function offsetGet(mixed $offset): Block {
        return $this->blocks[$this->offset($offset)];
    }

    /**
     * @param Block|null $offset
     * @param Block $value
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        $offset = $this->offset($offset ?? $value);

        if ($offset !== null) {
            $this->blocks[$offset] = $value;
        } else {
            $this->blocks[] = $value;
        }

        parent::reset();
    }

    /**
     * @param Block $offset
     */
    #[Override]
    public function offsetUnset(mixed $offset): void {
        unset($this->blocks[$this->offset($offset)]);

        parent::reset();
    }

    private function offset(?Block $offset): ?string {
        $offset = $offset instanceof NamedBlock ? $offset->getName() : null;
        $offset = $offset !== '' ? $offset : null;

        return $offset;
    }
    // </editor-fold>
}
