<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals;

use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Flag;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Message;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Status;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Verbosity;
use LogicException;
use UnexpectedValueException;

use function implode;
use function sprintf;

/**
 * @internal
 */
abstract class Block {
    protected ?Status $status       = null;
    protected float   $start        = 0;
    protected ?float  $end          = null;
    protected ?float  $time         = null;
    protected ?float  $timeSelf     = null;
    protected ?float  $timeTotal    = null;
    protected ?float  $timeChild    = null;
    protected ?float  $timeExternal = null;
    /**
     * @var list<self>
     */
    protected array      $children = [];
    protected Statistics $statistics;

    public function __construct(float $start) {
        $this->start      = $start;
        $this->statistics = new Statistics();
    }

    public function end(?Status $status, float $end): static {
        $this->status    = $status;
        $this->end       = $end;
        $this->time      = $this->end - $this->start;
        $this->timeTotal = $this->time - (float) $this->timeExternal;
        $this->timeSelf  = $this->timeTotal - (float) $this->timeChild;

        return $this;
    }

    /**
     * @template T of self
     *
     * @param T $block
     *
     * @return ?T
     */
    public function add(self $block): ?self {
        if ($block->time === null) {
            throw new LogicException('The `$block` is not finished yet?');
        }

        if (!$this->child($block)) {
            throw new UnexpectedValueException(
                sprintf(
                    'The `%s` expected to be a child of `%s`.',
                    $block::class,
                    $this::class,
                ),
            );
        }

        $this->statistics   = $this->statistics->merge($block->statistics);
        $this->children[]   = $block;
        $this->timeChild    = (float) $this->timeChild + (float) $block->timeTotal;
        $this->timeExternal = (float) $this->timeExternal + (float) $block->timeExternal;

        return null;
    }

    public function subtract(self $block): self {
        if ($block->time === null) {
            throw new LogicException('The `$block` is not finished yet?');
        }

        if ($this->child($block)) {
            throw new UnexpectedValueException(
                sprintf(
                    'The `%s` not expected to be a child of `%s`.',
                    $block::class,
                    $this::class,
                ),
            );
        }

        $this->timeExternal = (float) $this->timeExternal + $block->time;

        return $block;
    }

    abstract public function child(self $block): bool;

    /**
     * @param int<0, max> $padding
     *
     * @return iterable<Verbosity, iterable<mixed, string>>
     */
    abstract public function render(Renderer $renderer, Formatter $formatter, int $padding): iterable;

    /**
     * @param int<0, max> $padding
     *
     * @return iterable<Verbosity, iterable<mixed, string>>
     */
    protected function children(Renderer $renderer, Formatter $formatter, int $padding): iterable {
        foreach ($this->children as $child) {
            yield from $child->render($renderer, $formatter, $padding + 1);
        }
    }

    /**
     * @param int<0, max> $padding
     *
     * @return iterable<mixed, string>
     */
    protected function times(Renderer $renderer, Formatter $formatter, int $padding): iterable {
        yield from $renderer->run(Message::Self, $padding, Mark::Info, duration: $this->timeSelf);
    }

    /**
     * @param int<0, max> $padding
     *
     * @return iterable<mixed, string>
     */
    protected function statistics(Renderer $renderer, Formatter $formatter, int $padding): iterable {
        foreach (Flag::cases() as $flag) {
            if (!isset($this->statistics[$flag])) {
                continue;
            }

            yield from $renderer->run(
                match ($flag) {
                    Flag::Read   => Message::Read,
                    Flag::Write  => Message::Write,
                    Flag::Delete => Message::Delete,
                },
                $padding,
                Mark::Info,
                implode(' / ', [
                    $formatter->integer($this->statistics[$flag]->count),
                    $formatter->filesize($this->statistics[$flag]->bytes),
                ]),
                [],
                null,
                $this->statistics[$flag]->time,
            );
        }

        yield from [];
    }
}
