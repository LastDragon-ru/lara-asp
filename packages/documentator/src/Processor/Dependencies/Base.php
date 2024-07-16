<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Stringable;

use function dirname;

abstract class Base implements Stringable {
    protected function __construct() {
        // empty
    }

    protected function getPath(File $file): string {
        $base = dirname($file->getPath());
        $path = Path::getPath($base, (string) $this);

        return $path;
    }
}
