<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals\Blocks;

use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Verbosity;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals\Renderer;
use Override;

/**
 * @internal
 */
class Dependency extends Block {
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
