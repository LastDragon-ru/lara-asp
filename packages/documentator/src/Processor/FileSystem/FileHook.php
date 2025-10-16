<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;

/**
 * @internal
 */
class FileHook extends File {
    public function __construct(
        FileSystemAdapter $adapter,
        FilePath $path,
        Metadata $metadata,
        public readonly Hook $hook,
    ) {
        parent::__construct($adapter, $path, $metadata);
    }
}
