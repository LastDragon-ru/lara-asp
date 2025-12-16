<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Event;

readonly class FileSystemReadEnd implements Event {
    public function __construct(
        public FileSystemReadResult $result,
        /**
         * @var int<0, max>
         */
        public int $bytes,
    ) {
        // empty
    }
}
