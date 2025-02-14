<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Hooks;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;

class Hooks {
    /**
     * @param array<array-key, object> $context
     */
    public function __construct(
        protected array $context = [],
    ) {
        // empty
    }

    public function get(FileSystem $fs, Hook $hook): File {
        $path = $fs->input->getFilePath("@.{$hook->value}");
        $file = match ($hook) {
            Hook::Context => new HookFile($path, $this->context),
            default       => new HookFile($path),
        };

        return $file;
    }
}
