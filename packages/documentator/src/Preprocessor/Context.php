<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

class Context {
    public function __construct(
        public readonly Directory $root,
        public readonly Directory $directory,
        public readonly File $file,
        public readonly string $target,
        public readonly ?string $parameters,
    ) {
        // empty
    }
}
