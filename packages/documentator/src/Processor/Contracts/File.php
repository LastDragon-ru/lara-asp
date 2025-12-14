<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileImpl;
use LastDragon_ru\Path\FilePath;

/**
 * @property-read FilePath          $path
 * @property-read non-empty-string  $name
 * @property-read ?non-empty-string $extension
 * @property-read string            $content
 *
 * @phpstan-require-extends FileImpl
 */
interface File {
    // empty
}
