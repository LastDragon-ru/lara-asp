<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\Sources;

use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Verbosity;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\Source;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Renderer;
use Override;

/**
 * @internal
 */
class File extends Source {
    public function __construct(
        float $start,
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
            $this->path,
            $padding,
            $this->mark,
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
