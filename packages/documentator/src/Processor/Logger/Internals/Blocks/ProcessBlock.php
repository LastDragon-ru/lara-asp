<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Message;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Status;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Verbosity;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks\Sources\FileBlock;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Memory;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Renderer;
use LastDragon_ru\Path\DirectoryPath;
use Override;

use function func_num_args;
use function implode;

/**
 * @internal
 */
class ProcessBlock extends Block {
    protected int $memory          = 0;
    protected int $files           = 0;
    protected int $startMemory     = 0;
    protected int $startPeakMemory = 0;

    public function __construct(
        float $start,
        Memory $memory,
        public readonly DirectoryPath $input,
        public readonly DirectoryPath $output,
        /**
         * @var list<non-empty-string>
         */
        public readonly array $include,
        /**
         * @var list<non-empty-string>
         */
        public readonly array $exclude,
    ) {
        $this->startMemory     = $memory->current;
        $this->startPeakMemory = $memory->peak;

        parent::__construct($start);
    }

    #[Override]
    public function end(?Status $status, float $end, ?Memory $memory = null): static {
        if (func_num_args() < 3) {
            throw new InvalidArgumentException('The `$memory` is not set.');
        }

        $peakMemory         = $memory->peak ?? 0;
        $this->memory       = $peakMemory > $this->startPeakMemory ? $peakMemory - $this->startMemory : 0;
        $this->timeExternal = 0; // there is no external time for this block

        return parent::end($status, $end);
    }

    #[Override]
    public function add(Block $block): Block {
        $block          = parent::add($block) ?? $block;
        $this->children = []; // to save memory

        if ($block instanceof FileBlock) {
            $this->files++;
        }

        return $block;
    }

    #[Override]
    public function child(Block $block): bool {
        return $block instanceof SourceBlock;
    }

    /**
     * @param int<0, max> $padding
     *
     * @inheritDoc
     */
    #[Override]
    public function render(Renderer $renderer, Formatter $formatter, int $padding): iterable {
        if ($this->time === null) {
            yield Verbosity::Normal => $renderer->title(Message::Title, $padding);
            yield Verbosity::Normal => $renderer->properties($this->properties(), $padding + 1);
        } else {
            yield Verbosity::Debug => $this->times($renderer, $formatter, $padding);
            yield Verbosity::Verbose => $this->statistics($renderer, $formatter, $padding);
            yield Verbosity::Normal => $renderer->run(
                title  : Message::Files,
                padding: $padding,
                mark   : Mark::Info,
                value  : $formatter->integer($this->files),
            );
            yield Verbosity::Verbose => $renderer->run(
                title  : Message::Memory,
                padding: $padding,
                mark   : Mark::Info,
                value  : $this->memory > 0 ? $formatter->filesize($this->memory) : null,
            );
            yield Verbosity::Normal => $renderer->run(
                title   : $this->status === Status::Done ? Message::Completed : Message::Failed,
                padding : $padding,
                mark    : $this->status === Status::Done ? Mark::Done : Mark::Fail,
                status  : $this->status,
                duration: $this->time,
            );
        }
    }

    /**
     * @return iterable<mixed, array{Mark, Message, string}>
     */
    private function properties(): iterable {
        if ($this->input->equals($this->output)) {
            yield [Mark::Inout, Message::Inout, (string) $this->input];
        } else {
            yield [Mark::Input, Message::Input, (string) $this->input];
            yield [Mark::Output, Message::Output, (string) $this->output];
        }

        yield [Mark::Info, Message::Include, "'".implode("', '", $this->include)."'"];

        if ($this->exclude !== []) {
            yield [Mark::Info, Message::Exclude, "'".implode("', '", $this->exclude)."'"];
        }
    }
}
