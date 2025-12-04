<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use Iterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModified;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileReadFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileSaveFailed;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use WeakMap;

use function array_last;
use function array_pop;
use function count;
use function is_string;
use function spl_object_id;
use function sprintf;

/**
 * @property-read DirectoryPath $directory
 */
class FileSystem {
    /**
     * @var array<string, File>
     */
    private array $cache = [];
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
        private readonly Caster $caster,
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

        $this->content = new WeakMap();
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     */
    protected function isFile(FilePath $path): bool {
        $path = $this->input->resolve($path);
        $file = $this->cached($path);
        $is   = $file !== null || $this->adapter->isFile($path);

        return $is;
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     */
    public function getFile(FilePath $path): File {
        // Cached?
        $path = $this->input->resolve($path);
        $file = $this->cached($path);

        if ($file instanceof File) {
            return $file;
        }

        // Create
        if ($this->adapter->isFile($path)) {
            $file = new File($this, $path, $this->caster);
        } else {
            throw new FileNotFound($path);
        }

        return $this->cache($file);
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     *
     * @param list<string> $include
     * @param list<string> $exclude
     *
     * @return Iterator<array-key, File>
     */
    public function getFilesIterator(
        DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): Iterator {
        // Exist?
        $directory = $this->input->resolve($directory);

        if (!$this->adapter->isDirectory($directory)) {
            throw new DirectoryNotFound($directory);
        }

        // Search
        $iterator = $this->adapter->getFilesIterator($directory, $include, $exclude, $depth);

        foreach ($iterator as $path) {
            $path = $directory->resolve($path);
            $file = $this->cached($path) ?? $this->cache(new File($this, $path, $this->caster));

            yield $file;
        }

        yield from [];
    }

    public function read(File $file): string {
        if (!isset($this->content[$file])) {
            try {
                $this->content[$file] = $this->adapter->read($file->path);
            } catch (Exception $exception) {
                throw new FileReadFailed($file->path, $exception);
            }
        }

        return $this->content[$file];
    }

    /**
     * If `$file` exists, it will be saved only after {@see self::commit()},
     * if not, it will be created immediately. Relative path will be resolved
     * based on {@see self::$output}.
     */
    public function write(File|FilePath $path, object|string $content): File {
        // Prepare
        $file = null;

        if ($path instanceof File) {
            $file = $path;
            $path = $path->path;
        } else {
            // as is
        }

        // Relative?
        $path = $this->output->resolve($path);

        // Writable?
        if (!$this->output->contains($path)) {
            throw new FileNotWritable($path);
        }

        // File?
        $file ??= $this->isFile($path) ? $this->getFile($path) : null;
        $exists = $file !== null;
        $file ??= $this->cache(new File($this, $path, $this->caster));

        // Changed?
        if (!is_string($content)) {
            $content = $this->caster->castFrom($file, $content);
        }

        if ($content === null || (($this->content[$file] ?? null) === $content)) {
            return $file;
        }

        // Update
        $this->content[$file] = $content;

        if ($exists) {
            $this->queue($file);
        } else {
            $this->save($file);
        }

        // Event
        $this->dispatcher->notify(
            new FileSystemModified(
                $file->path,
                $exists
                    ? FileSystemModifiedType::Updated
                    : FileSystemModifiedType::Created,
            ),
        );

        // Return
        return $file;
    }

    public function begin(DirectoryPath $path): void {
        $this->level[] = $path;
    }

    public function commit(): void {
        // Level
        array_pop($this->level);

        if (count($this->level) > 0) {
            return;
        }

        // Commit
        while (count($this->queue) > 0) {
            $this->save(array_pop($this->queue));
        }

        // Cleanup
        $this->level   = [];
        $this->cache   = [];
        $this->queue   = [];
        $this->content = new WeakMap();
    }

    protected function queue(File $file): void {
        if (count($this->level) > 0) {
            $this->queue[spl_object_id($file)] = $file;
        } else {
            $this->save($file);
        }
    }

    protected function save(File $file): void {
        try {
            if (isset($this->content[$file])) {
                $this->adapter->write($file->path, $this->content[$file]);
            }
        } catch (Exception $exception) {
            throw new FileSaveFailed($file->path, $exception);
        }
    }

    /**
     * @template T of File
     *
     * @param T $object
     *
     * @return T
     */
    private function cache(File $object): File {
        $this->cache[$object->path->normalized()->path] = $object;

        return $object;
    }

    private function cached(FilePath $path): ?File {
        $cached = $this->cache[$path->normalized()->path] ?? null;

        return $cached;
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
}
