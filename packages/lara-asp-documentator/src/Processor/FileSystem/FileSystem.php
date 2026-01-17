<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemDeleteResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemReadResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteBegin;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteEnd;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemWriteResult;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\PathUnavailable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileImpl;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

use function array_last;
use function array_pop;
use function sprintf;
use function str_starts_with;
use function strlen;

/**
 * @property-read DirectoryPath $directory
 */
class FileSystem {
    private Cache   $cache;
    private Content $content;
    /**
     * @var array<int, DirectoryPath>
     */
    private array $level = [];

    public function __construct(
        private readonly Adapter $adapter,
        private readonly Dispatcher $dispatcher,
        public readonly DirectoryPath $input,
        public readonly DirectoryPath $output,
    ) {
        if ($input->relative) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$input` path must be absolute, `%s` given.',
                    $input,
                ),
            );
        }

        if ($output->relative) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$output` path must be absolute, `%s` given.',
                    $input,
                ),
            );
        }

        $this->cache   = new Cache(50);
        $this->content = new Content();
    }

    public function exists(FilePath $path): bool {
        // Fast check
        if (isset($this->cache[$path])) {
            return true;
        }

        // Check
        $path   = $this->path($path);
        $file   = $this->cache[$path];
        $file   = $file === null && $this->adapter->exists($path)
            ? $this->make($path) // cache to prevent another `exists()` call
            : $file;
        $exists = $file !== null;

        return $exists;
    }

    public function get(FilePath $path): File {
        // Fast check
        if (isset($this->cache[$path])) {
            return $this->cache[$path];
        }

        // Cached?
        $path = $this->path($path);
        $file = $this->cache[$path];

        if ($file instanceof File) {
            return $file;
        }

        // Exists?
        if (!$this->adapter->exists($path)) {
            throw new PathNotFound($path);
        }

        // Create
        return $this->make($path);
    }

    /**
     * @param list<non-empty-string> $exclude
     * @param list<non-empty-string> $include
     *
     * @return iterable<mixed, DirectoryPath|FilePath>
     */
    public function search(
        DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        bool $hidden = false,
    ): iterable {
        // Exist?
        $directory = $this->path($directory);

        if (!$this->adapter->exists($directory)) {
            throw new PathNotFound($directory);
        }

        // Search
        $iterator = $this->adapter->search($directory, $include, $exclude, $hidden);

        foreach ($iterator as $path) {
            yield $directory->resolve($path);
        }

        yield from [];
    }

    public function read(File $file): string {
        if (!isset($this->content[$file])) {
            $result = ($this->dispatcher)(new FileSystemReadBegin($file->path), FileSystemReadResult::Success);
            $bytes  = 0;

            try {
                $this->content[$file] = $this->adapter->read($file->path);
                $bytes                = strlen($this->content[$file]); // @phpstan-ignore disallowed.function (ok)
            } catch (Exception $exception) {
                $result = FileSystemReadResult::Error;

                throw $exception;
            } finally {
                ($this->dispatcher)(new FileSystemReadEnd($result, $bytes));
            }
        }

        return $this->content[$file];
    }

    /**
     * If `$file` exists, it will be saved only after {@see self::commit()},
     * if not, it will be created immediately.
     */
    public function write(File|FilePath $path, string $content): File {
        // Prepare
        $file = null;

        if ($path instanceof File) {
            $file = $path;
            $path = $path->path;
        } else {
            // as is
        }

        // Writable?
        $path = $this->path($path);

        if (!$this->output->contains($path)) {
            throw new PathNotWritable($path);
        }

        // File?
        $file ??= $this->exists($path) ? $this->get($path) : null;
        $exists = $file !== null;
        $file ??= $this->make($path);

        // Change
        $this->content[$file] = $content;

        if (!$exists) {
            $this->save($file);
        }

        // Return
        return $file;
    }

    public function delete(DirectoryPath|FilePath $path): void {
        // Writable?
        $path = $this->path($path);

        if (!$this->output->contains($path)) {
            throw new PathNotWritable($path);
        }

        // Delete
        $result = ($this->dispatcher)(new FileSystemDeleteBegin($path), FileSystemDeleteResult::Success);

        try {
            $this->adapter->delete($path);
            $this->content->delete($path);
            $this->cache->delete($path);
        } catch (Exception $exception) {
            $result = FileSystemDeleteResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new FileSystemDeleteEnd($result));
        }
    }

    public function begin(DirectoryPath $path): void {
        $this->level[] = $this->path($path);
    }

    public function commit(): void {
        // Level
        array_pop($this->level);

        // Dump
        foreach ($this->content->changes() as $file) {
            $this->save($file);
        }

        // Cleanup
        $this->cache->cleanup();
        $this->content->cleanup();
    }

    protected function save(File $file): void {
        if (!isset($this->content[$file])) {
            return;
        }

        $result = ($this->dispatcher)(new FileSystemWriteBegin($file->path), FileSystemWriteResult::Success);
        $bytes  = 0;

        try {
            $this->adapter->write($file->path, $this->content[$file]);
            $this->content->reset($file);

            $bytes = strlen($this->content[$file] ?? ''); // @phpstan-ignore disallowed.function (ok)
        } catch (Exception $exception) {
            $result = FileSystemWriteResult::Error;

            throw $exception;
        } finally {
            ($this->dispatcher)(new FileSystemWriteEnd($result, $bytes));
        }
    }

    /**
     * @template T of DirectoryPath|FilePath
     *
     * @param T $path
     *
     * @return new<T>
     */
    protected function path(DirectoryPath|FilePath $path): DirectoryPath|FilePath {
        $path = $path->normalized();

        if (!str_starts_with($path->path, $this->input->path) && !str_starts_with($path->path, $this->output->path)) {
            throw new PathUnavailable($path);
        }

        return $path;
    }

    /**
     * @deprecated 10.0.0 Will be replaced to property hooks soon.
     */
    public function __isset(string $name): bool {
        return $this->__get($name) !== null;
    }

    /**
     * @deprecated 10.0.0 Will be replaced to property hooks soon.
     */
    public function __get(string $name): mixed {
        return match ($name) {
            'directory' => array_last($this->level) ?? $this->input,
            default     => null,
        };
    }

    protected function make(FilePath $path): File {
        $file               = new FileImpl($this, $path);
        $this->cache[$path] = $file;

        return $file;
    }
}
