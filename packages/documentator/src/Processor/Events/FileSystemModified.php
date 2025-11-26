<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

use LastDragon_ru\Path\FilePath;

readonly class FileSystemModified implements Event {
    public function __construct(
        public FilePath $path,
        public FileSystemModifiedType $type,
    ) {
        // empty
    }
}
