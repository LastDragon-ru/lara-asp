<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use Iterator;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Dispatcher;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModified;
use LastDragon_ru\LaraASP\Documentator\Processor\Events\FileSystemModifiedType;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileCreateFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotWritable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileSaveFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;
use SplObjectStorage;

use function array_reverse;
use function explode;
use function is_object;
use function is_string;
use function sprintf;

class FileSystem {
    /**
     * @var SplObjectStorage<Hook, FileHook>
     */
    private SplObjectStorage $hooks;
    /**
     * @var array<string, Directory|File>
     */
    private array $cache = [];
    /**
     * @var array<int, array<string, string>>
     */
    private array $changes = [];
    private int   $level   = 0;

    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly Metadata $metadata,
        private readonly FileSystemAdapter $adapter,
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

        $this->hooks = new SplObjectStorage();
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     *
     * @return non-empty-string
     */
    public function getPathname(Directory|DirectoryPath|File|FilePath $path): string {
        $suffix = $path instanceof Directory || $path instanceof DirectoryPath ? '/' : '';
        $hook   = $path instanceof FileHook;
        $path   = $path instanceof Entry ? $path->getPath() : $path;
        $path   = $this->input->getPath($path);
        $name   = match (true) {
            $hook && $path instanceof FilePath
                => Mark::Hook->value.' :'.(array_reverse(explode(':', (string) $path->getExtension()))[0]),
            $this->input->isEqual($this->output) && $this->input->isInside($path),
                => Mark::Inout->value.' '.$this->output->getRelativePath($path).$suffix,
            $this->output->isInside($path),
            $this->output->isEqual($path),
                => Mark::Output->value.' '.$this->output->getRelativePath($path).$suffix,
            $this->input->isInside($path),
            $this->input->isEqual($path),
                => Mark::Input->value.' '.$this->input->getRelativePath($path).$suffix,
            default
                => Mark::External->value.' '.$path.$suffix,
        };

        return $name;
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     */
    protected function isFile(FilePath|string $path): bool {
        $path = $this->input->getFilePath((string) $path);
        $file = $this->cached($path);
        $is   = $file instanceof File || $this->adapter->isFile((string) $path);

        return $is;
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     */
    public function getFile(FilePath|Hook|string $path): File {
        // Hook?
        if ($path instanceof Hook) {
            return $this->hook($path);
        }

        // Cached?
        $path = $this->input->getFilePath((string) $path);
        $file = $this->cached($path);

        if ($file !== null && !($file instanceof File)) {
            throw new FileNotFound($path);
        }

        if ($file instanceof File) {
            return $file;
        }

        // Create
        if ($this->adapter->isFile((string) $path)) {
            $file = new FileReal($this->adapter, $path, $this->metadata);
        } else {
            throw new FileNotFound($path);
        }

        return $this->cache($file);
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     */
    public function getDirectory(DirectoryPath|FilePath|string $path): Directory {
        // Cached?
        $path      = $path instanceof FilePath ? $path->getDirectoryPath() : $path;
        $path      = $this->input->getDirectoryPath((string) $path);
        $directory = $this->cached($path);

        if ($directory !== null && !($directory instanceof Directory)) {
            throw new DirectoryNotFound($path);
        }

        if ($directory instanceof Directory) {
            return $directory;
        }

        // Create
        if ($this->adapter->isDirectory((string) $path)) {
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
        Directory|DirectoryPath|string $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): Iterator {
        $directory = $directory instanceof Directory ? $directory : $this->getDirectory($directory);
        $iterator  = $this->adapter->getFilesIterator((string) $directory, $include, $exclude, $depth);

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
        Directory|DirectoryPath|string $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): Iterator {
        $directory = $directory instanceof Directory ? $directory : $this->getDirectory($directory);
        $iterator  = $this->adapter->getDirectoriesIterator((string) $directory, $include, $exclude, $depth);

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
    public function write(File|FilePath|string $path, object|string $content): File {
        // Hook?
        if ($path instanceof FileHook) {
            throw new FileNotWritable($path->getPath());
        }

        // Prepare
        $file = null;

        if ($path instanceof File) {
            $file = $path;
            $path = $path->getPath();
        } elseif (is_string($path)) {
            $path = new FilePath($path);
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
        if ($file === null) {
            $file = !$this->isFile($path)
                ? new FileVirtual($this->adapter, $path, $this->metadata)
                : $this->getFile($path);
        }

        // Metadata?
        $metadata = null;

        if (is_object($content)) {
            $metadata = $content;
            $content  = $this->metadata->serialize($file, $metadata);
        }

        // Content
        if (!($metadata instanceof Content)) {
            $content = $this->metadata->serialize($file, new Content($content));
        }

        // File?
        $created = false;

        if ($file instanceof FileVirtual) {
            try {
                $this->adapter->write((string) $path, $content);
            } catch (Exception $exception) {
                throw new FileCreateFailed($path, $exception);
            }

            $file    = $this->getFile($path);
            $created = true;
        }

        // Changed?
        $updated = !$this->metadata->has($file, Content::class)
            || $this->metadata->get($file, Content::class)->content !== $content;

        if ($updated) {
            $this->metadata->reset($file);
            $this->metadata->set($file, new Content($content));

            if (!$created) {
                $this->change($file, $content);
            }
        }

        // Metadata
        if ($metadata !== null && !($metadata instanceof Content)) {
            $this->metadata->set($file, $metadata);
        }

        // Event
        if ($updated || $created) {
            $this->dispatcher->notify(
                new FileSystemModified(
                    $this->getPathname($file),
                    $created
                        ? FileSystemModifiedType::Created
                        : FileSystemModifiedType::Updated,
                ),
            );
        }

        // Return
        return $file;
    }

    public function begin(): void {
        $this->level++;
        $this->changes[$this->level] = [];
    }

    public function commit(): void {
        // Commit
        foreach ($this->changes[$this->level] ?? [] as $path => $content) {
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

    protected function change(File $path, string $content): void {
        $path                               = (string) $path;
        $this->changes[$this->level][$path] = $content;

        for ($level = $this->level - 1; $level >= 0; $level--) {
            unset($this->changes[$level][$path]);
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

    private function hook(Hook $hook): FileHook {
        if (!isset($this->hooks[$hook])) {
            $path               = $this->input->getFilePath("@.{$hook->value}");
            $this->hooks[$hook] = new FileHook($this->adapter, $path, $this->metadata, $hook);
        }

        return $this->hooks[$hook];
    }
}
