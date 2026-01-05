<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks;

use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Verbosity;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Renderer;
use Override;

/**
 * @internal
 */
class DependencyBlock extends Block {
    public function __construct(
        float $start,
        protected Mark $mark,
        protected string $path,
    ) {
        parent::__construct($start);
    }

    #[Override]
    public function child(Block $block): bool {
        return false;
    }

    /**
     * @param int<0, max> $padding
     *
     * @inheritDoc
     */
    #[Override]
    public function render(Renderer $renderer, Formatter $formatter, int $padding): iterable {
        yield Verbosity::Debug => $renderer->run(
            $this->path,
            $padding,
            $this->mark,
            null,
            $this->statistics->flags(),
            $this->status,
        );
    }
}
