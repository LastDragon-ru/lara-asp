<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals;

use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Formatter;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Contracts\Output;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Flag;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Status;

use function array_map;
use function count;
use function max;

/**
 * @internal
 */
readonly class Width {
    /**
     * @var positive-int
     */
    public int $total;
    /**
     * @var positive-int
     */
    public int $flags;
    /**
     * @var positive-int
     */
    public int $status;
    /**
     * @var positive-int
     */
    public int $duration;

    public function __construct(
        protected Output $output,
        protected Formatter $formatter,
        /**
         * @var int<0, max>
         */
        public int $padding,
        /**
         * @var int<0, max>
         */
        public int $mark,
    ) {
        $this->total    = $this->getTotalWidth();
        $this->flags    = $this->getFlagsWidth();
        $this->status   = $this->getStatusWidth();
        $this->duration = $this->getDurationWidth();
    }

    /**
     * @return int<0, max>
     */
    public function width(string $string): int {
        return $this->output->length($string);
    }

    /**
     * @return positive-int
     */
    private function getTotalWidth(): int {
        return $this->output->width;
    }

    /**
     * @return positive-int
     */
    private function getFlagsWidth(): int {
        return count(Flag::cases());
    }

    /**
     * @return positive-int
     */
    private function getStatusWidth(): int {
        $statuses = array_map($this->formatter->status(...), Status::cases());
        $statuses = array_map($this->output->length(...), $statuses);
        $statuses = max($statuses);
        $statuses = max(1, $statuses);

        return $statuses;
    }

    /**
     * @return positive-int
     */
    private function getDurationWidth(): int {
        $duration = $this->formatter->duration(25 * 60 + 25.123);
        $duration = $this->output->length($duration);
        $duration = max(1, $duration);

        return $duration;
    }
}
