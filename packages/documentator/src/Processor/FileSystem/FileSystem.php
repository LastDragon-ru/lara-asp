<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use Iterator;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
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
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;

use function array_reverse;
use function explode;
use function is_dir;
use function is_file;
use function is_object;
use function sprintf;

class FileSystem {
    /**
     * @var array<string, Directory|File>
     */
    private array $cache = [];
    /**
     * @var array<int, array<string, string>>
     */
    private array $changes = [];
    private int   $level   = 0;

    private readonly SymfonyFilesystem $filesystem;

    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly Metadata $metadata,
        public readonly DirectoryPath $input,
        public readonly DirectoryPath $output,
        public readonly bool $consistent = false,
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

        $this->filesystem = new SymfonyFilesystem();
    }

    /**
     * @return non-empty-string
     */
    public function getPathname(Directory|DirectoryPath|File|FilePath $path): string {
        $suffix = $path instanceof Directory || $path instanceof DirectoryPath ? '/' : '';
        $hook   = $path instanceof FileHook;
        $path   = $path instanceof Entry ? $path->getPath() : $path;
        $name   = match (true) {
            $hook && $path instanceof FilePath
                => Mark::Hook->value.' :'.(array_reverse(explode(':', (string) $path->getExtension()))[0]),
            $this->input->isEqual($this->output),
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

    protected function isFile(FilePath|string $path): bool {
        $path = $this->input->getFilePath((string) $path);
        $file = $this->cached($path);
        $is   = $file instanceof File || is_file((string) $path);

        return $is;
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     */
    public function getFile(FilePath|Hook|string $path): File {
        // Hook?
        $hook = null;

        if ($path instanceof Hook) {
            $hook = $path;
            $path = "@.{$hook->value}";
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
        if ($hook !== null) {
            $file = new FileHook($this->metadata, $path, $hook);
        } elseif (is_file((string) $path)) {
            $file = new FileReal($this->metadata, $path);
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
        if (is_dir((string) $path)) {
            $directory = $this->cache(new Directory($path));
        } else {
            throw new DirectoryNotFound($path);
        }

        return $directory;
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     *
     * @param array<array-key, string>|string|null $include
     * @param array<array-key, string>|string|null $exclude
     *
     * @return Iterator<array-key, File>
     */
    public function getFilesIterator(
        Directory|DirectoryPath|string $directory,
        array|string|null $include = null,
        array|string|null $exclude = null,
        ?int $depth = null,
    ): Iterator {
        $finder = $this->getFinder($directory, $include, $exclude, $depth);

        foreach ($finder->files() as $info) {
            yield $this->getFile($info->getPathname());
        }

        yield from [];
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     *
     * @param array<array-key, string>|string|null $exclude
     * @param array<array-key, string>|string|null $include
     *
     * @return Iterator<array-key, Directory>
     */
    public function getDirectoriesIterator(
        Directory|DirectoryPath|string $directory,
        array|string|null $include = null,
        array|string|null $exclude = null,
        ?int $depth = null,
    ): Iterator {
        $finder = $this->getFinder($directory, $include, $exclude, $depth);

        foreach ($finder->directories() as $info) {
            yield $this->getDirectory($info->getPathname());
        }

        yield from [];
    }

    /**
     * @param array<array-key, string>|string|null $exclude
     * @param array<array-key, string>|string|null $include
     */
    private function getFinder(
        Directory|DirectoryPath|string $directory,
        array|string|null $include = null,
        array|string|null $exclude = null,
        ?int $depth = null,
    ): Finder {
        $directory = $directory instanceof Directory ? $directory : $this->getDirectory($directory);
        $finder    = Finder::create()
            ->ignoreVCSIgnored(true)
            ->exclude('node_modules')
            ->exclude('vendor')
            ->in((string) $directory);

        if ($this->consistent) {
            $finder = $finder->sortByName(true);
        }

        if ($include !== null) {
            $finder = $finder->name($include);
        }

        if ($depth !== null) {
            $finder = $finder->depth("<= {$depth}");
        }

        if ($exclude !== null) {
            $exclude = (new Globs((array) $exclude))->regexp;

            if ($exclude !== null) {
                $finder = $finder->notPath($exclude);
            }
        }

        return $finder;
    }

    /**
     * If `$file` exists, it will be saved only after {@see self::commit()},
     * if not, it will be created immediately. Relative path will be resolved
     * based on {@see self::$output}.
     */
    public function write(File|FilePath|string $path, object|string $content): File {
        // Prepare
        $file = null;

        if ($path instanceof File) {
            $file = $path;
            $path = $path->getPath();
        } else {
            $file = $this->isFile($path) ? $this->getFile($path) : null;
            $path = $path instanceof FilePath ? $path : new FilePath($path);
        }

        // Relative?
        $path = $this->output->getPath($path);

        // Writable?
        if (!$this->output->isInside($path)) {
            throw new FileNotWritable($path);
        }

        // Metadata?
        $metadata = null;

        if (is_object($content)) {
            $metadata = $content;
            $content  = $this->metadata->serialize($path, $metadata);
        }

        // Content
        if (!($metadata instanceof Content)) {
            $content = $this->metadata->serialize($path, new Content($content));
        }

        // File?
        $created = false;

        if ($file === null) {
            try {
                $this->save($path, $content);
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
                $this->save($path, $content);
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
        $this->changes[$this->level][(string) $path] = $content;
    }

    protected function save(FilePath|string $path, string $content): void {
        $this->filesystem->dumpFile((string) $path, $content);
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
