<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
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
use WeakMap;

use function array_last;
use function array_pop;
use function count;
use function spl_object_id;
use function sprintf;
use function str_starts_with;
use function strlen;

/**
 * By default, relative paths will be resolved based on {@see self::$directory}.
 *
 * @property-read DirectoryPath $directory
 */
class FileSystem {
    private Cache $cache;
    /**
     * @var array<int, File>
     */
    private array $queue = [];
    /**
     * @var array<int, DirectoryPath>
     */
    private array $level = [];

    /**
     * @var WeakMap<File, string>
     */
    private WeakMap $content;

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
        $this->content = new WeakMap();
    }

    public function exists(FilePath $path): bool {
        $path   = $this->path($path);
        $file   = $this->cache[$path];
        $file   = $file === null && $this->adapter->exists($path)
            ? $this->make($path) // cache to prevent another `exists()` call
            : $file;
        $exists = $file !== null;

        return $exists;
    }

    public function get(FilePath $path): File {
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
            $path = $this->path($directory->resolve($path));

            if ($path instanceof FilePath && !isset($this->cache[$path])) {
                /**
                 * We are expecting all files are exist, so add them into the
                 * cache to avoid another {@see Adapter::exists()} call.
                 */
                $this->cache[$path] = new FileImpl($this, $path);
            }

            yield $path;
        }

        yield from [];
    }

    public function read(File $file): string {
        if (!isset($this->content[$file])) {
            $result = ($this->dispatcher)(new FileSystemReadBegin($file->path), FileSystemReadResult::Success);
            $bytes  = 0;

            try {
                $this->content[$file] = $this->adapter->read($file->path);
                $bytes                = strlen($this->content[$file]); // @phpstan-ignore disallowed.function (bytes)
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
     * if not, it will be created immediately. Relative path will be resolved
     * based on {@see self::$output}.
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

        // Changed?
        if (($this->content[$file] ?? null) === $content) {
            return $file;
        }

        // Update
        $this->content[$file] = $content;

        if ($exists) {
            $this->queue($file);
        } else {
            $this->save($file);
        }

        // Return
        return $file;
    }

    public function begin(DirectoryPath $path): void {
        $this->level[] = $this->path($path);
    }

    public function commit(): void {
        // Level
        array_pop($this->level);

        // Dump
        while (count($this->queue) > 0) {
            $this->save(array_pop($this->queue));
        }

        // Cleanup
        $this->cache->cleanup();
    }

    protected function queue(File $file): void {
        if (count($this->level) > 0) {
            $this->queue[spl_object_id($file)] = $file;
        } else {
            $this->save($file);
        }
    }

    protected function save(File $file): void {
        if (!isset($this->content[$file])) {
            return;
        }

        $result = ($this->dispatcher)(new FileSystemWriteBegin($file->path), FileSystemWriteResult::Success);
        $bytes  = 0;

        try {
            $this->adapter->write($file->path, $this->content[$file]);

            $bytes = strlen($this->content[$file]); // @phpstan-ignore disallowed.function (bytes)
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
     * @deprecated %{VERSION} Will be replaced to property hooks soon.
     */
    public function __isset(string $name): bool {
        return $this->__get($name) !== null;
    }

    /**
     * @deprecated %{VERSION} Will be replaced to property hooks soon.
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
