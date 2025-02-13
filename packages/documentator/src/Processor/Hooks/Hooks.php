<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Hooks;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;

class Hooks {
    public function __construct() {
        // empty
    }

    public function get(FileSystem $fs, Hook $hook): File {
        $path = $fs->input->getFilePath("$.{$hook->value}");
        $file = new HookFile($path);

        return $file;
    }
}
