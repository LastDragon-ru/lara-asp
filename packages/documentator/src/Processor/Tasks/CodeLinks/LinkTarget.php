<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use Override;
use Stringable;

readonly class LinkTarget implements Stringable {
    public function __construct(
        public FilePath $target,
        public bool $deprecated,
        /**
         * @var positive-int|null
         */
        public ?int $startLine,
        /**
         * @var positive-int|null
         */
        public ?int $endLine,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        $start    = $this->startLine;
        $end      = $this->endLine;
        $fragment = match (true) {
            $start > 0 && $end > 0 && $start !== $end => "#L{$start}-L{$end}",
            $start > null                             => "#L{$start}",
            default                                   => '',
        };
        $string = "{$this->target}{$fragment}";

        return $string;
    }
}
