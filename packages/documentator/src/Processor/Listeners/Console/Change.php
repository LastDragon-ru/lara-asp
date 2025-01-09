<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;

readonly class Change {
    public function __construct(
        public string $path,
        public FileSystemModifiedType $type,
    ) {
        // empty
    }
}
