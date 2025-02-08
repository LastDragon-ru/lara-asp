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

use function is_dir;
use function is_file;
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
        $path   = $path instanceof Entry ? $path->getPath() : $path;
        $name   = match (true) {
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
    public function getFile(FilePath|string $path): File {
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
        if (is_file((string) $path)) {
            $file = $this->cache(new File($this->metadata, $path));
        } else {
            throw new FileNotFound($path);
        }

        return $file;
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
     * @param array<array-key, string>|string|null         $patterns {@see Finder::name()}
     * @param array<array-key, string|int>|string|int|null $depth    {@see Finder::depth()}
     * @param array<array-key, string>|string|null         $exclude  {@see Finder::notPath()}
     *
     * @return Iterator<array-key, File>
     */
    public function getFilesIterator(
        Directory|DirectoryPath|string $directory,
        array|string|null $patterns = null,
        array|string|int|null $depth = null,
        array|string|null $exclude = null,
    ): Iterator {
        $finder = $this->getFinder($directory, $patterns, $depth, $exclude);

        foreach ($finder->files() as $info) {
            yield $this->getFile($info->getPathname());
        }

        yield from [];
    }

    /**
     * Relative path will be resolved based on {@see self::$input}.
     *
     * @param array<array-key, string>|string|null         $patterns {@see Finder::name()}
     * @param array<array-key, string|int>|string|int|null $depth    {@see Finder::depth()}
     * @param array<array-key, string>|string|null         $exclude  {@see Finder::notPath()}
     *
     * @return Iterator<array-key, Directory>
     */
    public function getDirectoriesIterator(
        Directory|DirectoryPath|string $directory,
        array|string|null $patterns = null,
        array|string|int|null $depth = null,
        array|string|null $exclude = null,
    ): Iterator {
        $finder = $this->getFinder($directory, $patterns, $depth, $exclude);

        foreach ($finder->directories() as $info) {
            yield $this->getDirectory($info->getPathname());
        }

        yield from [];
    }

    /**
     * @param array<array-key, string>|string|null         $patterns {@see Finder::name()}
     * @param array<array-key, string|int>|string|int|null $depth    {@see Finder::depth()}
     * @param array<array-key, string>|string|null         $exclude  {@see Finder::notPath()}
     */
    protected function getFinder(
        Directory|DirectoryPath|string $directory,
        array|string|null $patterns = null,
        array|string|int|null $depth = null,
        array|string|null $exclude = null,
    ): Finder {
        $directory = $directory instanceof Directory ? $directory : $this->getDirectory($directory);
        $finder    = Finder::create()
            ->ignoreVCSIgnored(true)
            ->exclude('node_modules')
            ->exclude('vendor')
            ->in((string) $directory)
            ->sortByName(true);

        if ($patterns !== null) {
            $finder = $finder->name($patterns);
        }

        if ($depth !== null) {
            $finder = $finder->depth($depth);
        }

        if ($exclude !== null) {
            $finder = $finder->notPath($exclude);
        }

        return $finder;
    }

    /**
     * If `$file` exists, it will be saved only after {@see self::commit()},
     * if not, it will be created immediately. Relative path will be resolved
     * based on {@see self::$output}.
     */
    public function write(File|FilePath|string $path, string $content): File {
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
