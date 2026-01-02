<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task as TaskContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Verbosity;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Renderer;
use Override;

/**
 * @internal
 */
class Task extends Block {
    /**
     * @param class-string<TaskContract> $task
     */
    public function __construct(
        float $start,
        protected string $task,
    ) {
        parent::__construct($start);
    }

    #[Override]
    public function child(Block $block): bool {
        return $block instanceof Dependency
            || $block instanceof Change;
    }

    /**
     * @param int<0, max> $padding
     *
     * @inheritDoc
     */
    #[Override]
    public function render(Renderer $renderer, Formatter $formatter, int $padding): iterable {
        yield Verbosity::Verbose => $renderer->run(
            $formatter->task($this->task),
            $padding,
            Mark::Task,
            null,
            $this->statistics->flags(),
            $this->status,
            $this->timeTotal,
        );

        yield Verbosity::Debug => $this->times($renderer, $formatter, $padding + 1);
        yield Verbosity::VeryVerbose => $this->statistics($renderer, $formatter, $padding + 1);

        yield from $this->children($renderer, $formatter, $padding);
    }
}
