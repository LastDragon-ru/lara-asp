<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use ArrayAccess;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use WeakMap;

/**
 * @internal
 * @implements ArrayAccess<File, string>
 */
class Content implements ArrayAccess {
    /**
     * @var WeakMap<File, string>
     */
    private WeakMap $files;
    /**
     * @var WeakMap<File, true>
     */
    private WeakMap $changes;

    public function __construct() {
        $this->files   = new WeakMap();
        $this->changes = new WeakMap();
    }

    public function changed(File $file): bool {
        return isset($this->changes[$file]);
    }

    /**
     * @return list<File>
     */
    public function changes(): array {
        $changes = [];

        foreach ($this->changes as $file => $unused) {
            if (isset($this->files[$file])) {
                $changes[] = $file;
            }
        }

        return $changes;
    }

    public function cleanup(): void {
        // Seems nothing to do?
    }

    public function delete(DirectoryPath|FilePath $path): void {
        $delete = [];

        foreach ($this->files as $file => $content) {
            if ($path->equals($file->path) || ($path instanceof DirectoryPath && $path->contains($file->path))) {
                $delete[] = $file;
            }
        }

        foreach ($delete as $file) {
            unset($this[$file]);
        }
    }

    public function reset(File $file): void {
        unset($this->changes[$file]);
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        return isset($this->files[$offset]);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        return $this->files[$offset] ?? null;
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        // Null? ($content[] = '...')
        if ($offset === null) {
            return;
        }

        // Unchanged?
        if (isset($this->files[$offset]) && $this->files[$offset] === $value) {
            return;
        }

        // Mark
        if (isset($this->files[$offset])) {
            $this->changes[$offset] = true;
        }

        // Save
        $this->files[$offset] = $value;
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        unset($this->changes[$offset]);
        unset($this->files[$offset]);
    }
}
