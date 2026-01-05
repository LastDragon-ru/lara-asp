<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use ArrayAccess;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use SplObjectStorage;
use WeakReference;

/**
 * @internal
 * @implements ArrayAccess<FilePath, File>
 */
class Cache implements ArrayAccess {
    /**
     * @var SplObjectStorage<File, int>
     */
    private SplObjectStorage $lifetimes;
    /**
     * @var array<string, WeakReference<File>>
     */
    private array $files = [];

    public function __construct(
        /**
         * @var positive-int
         */
        private int $lifetime,
    ) {
        $this->lifetimes = new SplObjectStorage();
    }

    public function cleanup(): void {
        // We are holding files until lifetime below or equal to zero, but still
        // hold reference in case if the file uses somewhere else.
        foreach ($this->files as $path => $reference) {
            // In use?
            $file = $reference->get();

            if ($file === null) {
                unset($this->files[$path]);

                continue;
            }

            // Decrease/Delete
            if (isset($this->lifetimes[$file]) && $this->lifetimes[$file] > 0) {
                $this->lifetimes[$file] = $this->lifetimes[$file] - 1;
            } else {
                unset($this->lifetimes[$file]);
            }
        }
    }

    public function delete(DirectoryPath|FilePath $path): void {
        if ($path instanceof DirectoryPath) {
            foreach ($this->files as $reference) {
                $file = $reference->get()->path ?? null;

                if ($file !== null && $path->contains($file)) {
                    $this->delete($file);
                }
            }
        } else {
            unset($this[$path]);
        }
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        return isset($this->files[$offset->path])
            && $this->files[$offset->path]->get() !== null;
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        $path = $offset->path;
        $file = ($this->files[$path] ?? null)?->get();

        if ($file !== null) {
            $this->lifetimes[$file] = ($this->lifetimes[$file] ?? ($this->lifetime - 1)) + 1;
        }

        return $file;
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        $offset                   ??= $value->path;
        $this->lifetimes[$value]    = $this->lifetime;
        $this->files[$offset->path] = WeakReference::create($value);
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        $path = $offset->path;
        $file = ($this->files[$path] ?? null)?->get();

        unset($this->files[$path]);

        if ($file !== null) {
            unset($this->lifetimes[$file]);
        }
    }
}
