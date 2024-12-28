<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Iterator;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileNotFound;
use Symfony\Component\Finder\Finder;

use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;

class FileSystem {
    /**
     * @var array<string, Directory|File>
     */
    private array                 $cache = [];
    public readonly DirectoryPath $input;
    public readonly DirectoryPath $output;

    public function __construct(DirectoryPath $input, ?DirectoryPath $output = null) {
        $this->input  = $input;
        $this->output = $output ?? $this->input;
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
            $file = $this->cache(new File($path));
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

    public function commit(): void {
        $this->cache = [];
    }

    public function save(File $file): bool {
        // Modified?
        if (!$file->isModified()) {
            return true;
        }

        // Inside?
        if ($this->output->isInside($file->getPath()) !== true) {
            return false;
        }

        // Directory?
        $directory = (string) $file->getDirectoryPath();

        if (!is_dir($directory) && !mkdir($directory, recursive: true)) {
            return false;
        }

        // Save
        return file_put_contents((string) $file->getPath(), $file->getContent()) !== false;
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
