<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

readonly class FileSystemModified implements Event {
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $path,
        public FileSystemModifiedType $type,
    ) {
        // empty
    }
}
