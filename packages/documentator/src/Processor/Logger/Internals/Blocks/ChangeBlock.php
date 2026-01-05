<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Blocks;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Flag;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Mark;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Status;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Verbosity;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Block;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Renderer;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals\Usage;
use Override;

use function func_num_args;

/**
 * @internal
 */
abstract class ChangeBlock extends Block {
    /**
     * @var ?int<0, max>
     */
    protected ?int $bytes = null;

    protected function __construct(
        float $start,
        protected readonly Flag $flag,
        protected readonly Mark $mark,
        protected string $path,
    ) {
        parent::__construct($start);
    }

    /**
     * @param ?int<0, max> $bytes
     */
    #[Override]
    public function end(?Status $status, float $end, ?int $bytes = null): static {
        if (func_num_args() < 3) {
            throw new InvalidArgumentException('The `$bytes` is not set.');
        }

        $block                         = parent::end($status, $end);
        $this->bytes                   = $bytes;
        $this->statistics[$this->flag] = new Usage((float) $this->timeTotal, 1, (int) $this->bytes);

        return $block;
    }

    #[Override]
    public function child(Block $block): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function render(Renderer $renderer, Formatter $formatter, int $padding): iterable {
        yield Verbosity::VeryVerbose => $renderer->run(
            $this->path,
            $padding,
            $this->mark,
            $this->bytes !== null ? $formatter->filesize($this->bytes) : null,
            $this->statistics->flags(),
            $this->status,
            $this->timeTotal,
        );
    }
}
