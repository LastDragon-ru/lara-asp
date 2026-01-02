<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals\Blocks\Sources;

use LastDragon_ru\LaraASP\Documentator\Processor\Hook as HookEnum;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Verbosity;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals\Blocks\Source;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals\Renderer;
use Override;

/**
 * @internal
 */
class Hook extends Source {
    public function __construct(
        float $start,
        protected HookEnum $hook,
        protected Mark $mark,
        protected string $path,
    ) {
        parent::__construct($start);
    }

    /**
     * @param int<0, max> $padding
     *
     * @inheritDoc
     */
    #[Override]
    public function render(Renderer $renderer, Formatter $formatter, int $padding): iterable {
        yield Verbosity::Normal => $renderer->run(
            "{$formatter->hook($this->hook)} {$formatter->mark($this->mark)} {$this->path}",
            $padding,
            Mark::Hook,
            null,
            $this->statistics->flags(),
            $this->status,
            $this->timeTotal,
        );

        yield Verbosity::Debug => $this->times($renderer, $formatter, $padding + 1);
        yield Verbosity::Verbose => $this->statistics($renderer, $formatter, $padding + 1);

        yield from $this->children($renderer, $formatter, $padding);
    }
}
