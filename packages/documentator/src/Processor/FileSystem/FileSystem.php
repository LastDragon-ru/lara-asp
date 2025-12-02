<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use Iterator;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModified;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileCreateFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileSaveFailed;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

use function array_key_last;
use function count;
use function is_string;
use function sprintf;

class FileSystem {
    /**
     * @var array<string, File>
     */
    private array $cache = [];
    /**
     * @var array<int, array<string, File>>
     */
    private array $changes = [];

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
            $file = new File($path, $this->caster);
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
            yield $this->getFile($path);
        }

        yield from [];
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
        $file ??= $this->cache(new File($path, $this->caster));

        // Changed?
        $content = is_string($content)
            ? $this->caster->castFrom($file, new Content($content))
            : $this->caster->castFrom($file, $content);

        if ($content === null) {
            return $file;
        }

        // Update
        if ($exists) {
            $this->change($file);
        } else {
            try {
                $this->adapter->write($path, $content);
            } catch (Exception $exception) {
                throw new FileCreateFailed($path, $exception);
            }
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

    public function begin(): void {
        $this->changes[] = [];
    }

    public function commit(): void {
        // Commit
        $level = array_key_last($this->changes);

        if ($level !== null) {
            foreach ($this->changes[$level] ?? [] as $file) {
                try {
                    $this->adapter->write($file->path, $this->caster->castTo($file, Content::class)->content);
                } catch (Exception $exception) {
                    throw new FileSaveFailed($file->path, $exception);
                }
            }

            unset($this->changes[$level]);
        }

        // Cleanup
        if (count($this->changes) <= 0) {
            $this->cache = [];
        }
    }

    protected function change(File $file): void {
        $level = array_key_last($this->changes);

        if ($level === null) {
            return;
        }

        $string                         = (string) $file->path;
        $this->changes[$level][$string] = $file;

        for ($l = $level - 1; $l >= 0; $l--) {
            unset($this->changes[$l][$string]);
        }
    }

    /**
     * @template T of File
     *
     * @param T $object
     *
     * @return T
     */
    protected function cache(File $object): File {
        $this->cache[(string) $object] = $object;

        return $object;
    }

    protected function cached(DirectoryPath|FilePath $path): ?File {
        $cached = $this->cache[(string) $path] ?? null;

        return $cached;
    }
}
