<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use LastDragon_ru\LaraASP\Core\Observer\Dispatcher;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;
use Traversable;

use function mb_strlen;

/**
 * @internal
 * @template TBlock of Block
 * @template TType
 * @extends BlockList<TBlock>
 */
abstract class UsageList extends BlockList {
    /**
     * @param Traversable<TType>|array<TType> $items
     */
    public function __construct(
        Dispatcher $dispatcher,
        Settings $settings,
        int $level,
        int $used,
        Traversable|array $items,
    ) {
        parent::__construct($dispatcher, $settings, $level, $used);

        foreach ($items as $item) {
            $this[] = $this->block($item);
        }
    }

    /**
     * @param TType $item
     */
    abstract protected function block(mixed $item): Block;

    abstract protected function separator(): string;

    abstract protected function prefix(): string;

    protected function getSeparator(): string {
        return "{$this->space()}{$this->separator()}{$this->space()}";
    }

    protected function getMultilineItemPrefix(): string {
        return "{$this->separator()}{$this->space()}";
    }

    protected function content(): string {
        $prefix  = $this->prefix();
        $content = parent::content();

        if ($content) {
            if ($this->isStringMultiline($content)) {
                $eol    = $this->eol();
                $indent = $this->indent();

                if ($prefix) {
                    $content = "{$prefix}{$eol}{$indent}{$content}";
                }
            } else {
                $space = $this->space();

                if ($prefix) {
                    $content = "{$prefix}{$space}{$content}";
                }
            }
        }

        return $content;
    }

    protected function getUsed(): int {
        return parent::getUsed() + mb_strlen("{$this->prefix()}{$this->space()}");
    }
}
