<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use Iterator;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
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

use function is_string;
use function sprintf;

class FileSystem {
    /**
     * @var array<string, Directory|File>
     */
    private array $cache = [];
    /**
     * @var array<int, array<string, array{FilePath, string}>>
     */
    private array $changes = [];
    private int   $level   = 0;

    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly Caster $caster,
        private readonly Adapter $adapter,
        public readonly DirectoryPath $input,
        public readonly DirectoryPath $output,
    ) {
        if (!$input->isAbsolute()) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$input` path must be absolute, `%s` given.',
                    $input,
                ),
            );
        }

        if (!$output->isAbsolute()) {
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
     *
     * @return non-empty-string
     */
    public function getPathname(Directory|DirectoryPath|File|FilePath $path): string {
        $path = $path instanceof Entry ? $path->getPath() : $path;
        $path = $this->input->getPath($path);
        $name = match (true) {
            $this->input->isEqual($this->output) && $this->input->isInside($path),
                => Mark::Inout->value.' '.$this->output->getRelativePath($path),
            $this->output->isInside($path),
            $this->output->isEqual($path),
                => Mark::Output->value.' '.$this->output->getRelativePath($path),
            $this->input->isInside($path),
            $this->input->isEqual($path),
                => Mark::Input->value.' '.$this->input->getRelativePath($path),
            default
                => Mark::External->value.' '.$path,
        };

        return $name;
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     */
    protected function isFile(FilePath $path): bool {
        $path = $this->input->getPath($path);
        $file = $this->cached($path);
        $is   = $file instanceof File || $this->adapter->isFile($path);

        return $is;
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     */
    public function getFile(FilePath $path): File {
        // Cached?
        $path = $this->input->getPath($path);
        $file = $this->cached($path);

        if ($file !== null && !($file instanceof File)) {
            throw new FileNotFound($path);
        }

        if ($file instanceof File) {
            return $file;
        }

        // Create
        if ($this->adapter->isFile($path)) {
            $file = new File($this->adapter, $path, $this->caster);
        } else {
            throw new FileNotFound($path);
        }

        return $this->cache($file);
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     */
    public function getDirectory(DirectoryPath|FilePath $path): Directory {
        // Cached?
        $path      = $path instanceof FilePath ? $path->getDirectoryPath() : $path;
        $path      = $this->input->getPath($path);
        $directory = $this->cached($path);

        if ($directory !== null && !($directory instanceof Directory)) {
            throw new DirectoryNotFound($path);
        }

        if ($directory instanceof Directory) {
            return $directory;
        }

        // Create
        if ($this->adapter->isDirectory($path)) {
            $directory = $this->cache(new Directory($this->adapter, $path));
        } else {
            throw new DirectoryNotFound($path);
        }

        return $directory;
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
        Directory|DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): Iterator {
        $directory = $directory instanceof Directory ? $directory : $this->getDirectory($directory);
        $iterator  = $this->adapter->getFilesIterator($directory->getPath(), $include, $exclude, $depth);

        foreach ($iterator as $path) {
            yield $this->getFile($path);
        }

        yield from [];
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     *
     * @param list<string> $exclude
     * @param list<string> $include
     *
     * @return Iterator<array-key, Directory>
     */
    public function getDirectoriesIterator(
        Directory|DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): Iterator {
        $directory = $directory instanceof Directory ? $directory : $this->getDirectory($directory);
        $iterator  = $this->adapter->getDirectoriesIterator($directory->getPath(), $include, $exclude, $depth);

        foreach ($iterator as $path) {
            yield $this->getDirectory($path);
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
            $path = $path->getPath();
        } else {
            // as is
        }

        // Relative?
        $path = $this->output->getPath($path);

        // Writable?
        if (!$this->output->isInside($path)) {
            throw new FileNotWritable($path);
        }

        // File?
        $file ??= $this->isFile($path) ? $this->getFile($path) : null;
        $exists = $file !== null;
        $file ??= $this->cache(new File($this->adapter, $path, $this->caster));

        // Changed?
        $content = is_string($content)
            ? $this->caster->castFrom($file, new Content($content))
            : $this->caster->castFrom($file, $content);

        if ($content === null) {
            return $file;
        }

        // Update
        if ($exists) {
            $this->change($file, $content);
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
                $this->getPathname($file),
                $exists
                    ? FileSystemModifiedType::Updated
                    : FileSystemModifiedType::Created,
            ),
        );

        // Return
        return $file;
    }

    public function begin(): void {
        $this->level++;
        $this->changes[$this->level] = [];
    }

    public function commit(): void {
        // Commit
        foreach ($this->changes[$this->level] ?? [] as [$path, $content]) {
            try {
                $this->adapter->write($path, $content);
            } catch (Exception $exception) {
                throw new FileSaveFailed($path, $exception);
            }
        }

        unset($this->changes[$this->level]);

        // Decrease
        $this->level--;

        // Cleanup
        if ($this->level === 0) {
            $this->changes = [];
            $this->cache   = [];
        }
    }

    protected function change(File $file, string $content): void {
        $path                                 = $file->getPath();
        $string                               = (string) $path;
        $this->changes[$this->level][$string] = [$path, $content];

        for ($level = $this->level - 1; $level >= 0; $level--) {
            unset($this->changes[$level][$string]);
        }
    }

    /**
     * @template T of Directory|File
     *
     * @param T $object
     *
     * @return T
     */
    protected function cache(Directory|File $object): Directory|File {
        $this->cache[(string) $object] = $object;

        return $object;
    }

    protected function cached(DirectoryPath|FilePath $path): Directory|File|null {
        $cached = $this->cache[(string) $path] ?? null;

        return $cached;
    }
}
