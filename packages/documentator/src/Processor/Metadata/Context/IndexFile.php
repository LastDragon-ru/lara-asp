<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Context;

use LastDragon_ru\LaraASP\Core\Path\FilePath;

readonly class IndexFile {
    public function __construct(
        public FilePath $path,
    ) {
        // empty
    }
}
