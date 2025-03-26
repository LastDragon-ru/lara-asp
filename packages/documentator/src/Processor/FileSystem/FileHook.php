<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;

/**
 * @internal
 */
class FileHook extends File {
    public function __construct(
        Metadata $metadata,
        FilePath $path,
        public readonly Hook $hook,
    ) {
        parent::__construct($metadata, $path);
    }
}
