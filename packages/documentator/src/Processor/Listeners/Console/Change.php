<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;

class Change {
    public function __construct(
        public readonly string $path,
        public readonly FileSystemModifiedType $type,
    ) {
        // empty
    }
}
