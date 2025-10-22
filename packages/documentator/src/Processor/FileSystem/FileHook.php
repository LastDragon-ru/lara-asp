<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;

/**
 * @internal
 */
class FileHook extends File {
    public function __construct(
        FileSystemAdapter $adapter,
        FilePath $path,
        Caster $caster,
        public readonly Hook $hook,
    ) {
        parent::__construct($adapter, $path, $caster);
    }
}
