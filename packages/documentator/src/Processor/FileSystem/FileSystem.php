<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Closure;
use Iterator;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Core\Path\Path;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use WeakReference;

use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;

class FileSystem {
    /**
     * @var array<string, WeakReference<Directory|File>>
     */
    private array $cache = [];

    public function __construct(
        private readonly ?DirectoryPath $output = null,
    ) {
        // empty
    }

    public function getFile(Directory $root, SplFileInfo|File|FilePath|string $path): ?File {
        // Object?
        if ($path instanceof File || $path instanceof FilePath) {
            $path = (string) $path;
        } elseif ($path instanceof SplFileInfo) {
            $path = $path->getPathname();
        } else {
            // empty
        }

        // Cached?
        $pathObject = $root->getPath()->getFilePath($path);
        $path       = (string) $pathObject;
        $file       = ($this->cache[$path] ?? null)?->get();

        if ($file !== null && !($file instanceof File)) {
            return null;
        }

        if ($file instanceof File) {
            return $file;
        }

        // Create
        if (is_file($path)) {
            $file               = new File($pathObject);
            $this->cache[$path] = WeakReference::create($file);
        }

        return $file;
    }

    public function getDirectory(Directory $root, SplFileInfo|Directory|File|Path|string $path): ?Directory {
        // Object?
        if ($path instanceof SplFileInfo) {
            $path = $path->getPathname();
        } elseif ($path instanceof Directory || $path instanceof File) {
            $path = (string) $path->getPath()->getDirectoryPath();
        } elseif ($path instanceof Path) {
            $path = (string) $path->getDirectoryPath();
        } else {
            // empty
        }

        // Self?
        if ($path === '.' || $path === '') {
            return $root;
        }

        // Cached?
        $pathObject = $root->getPath()->getDirectoryPath($path);
        $path       = (string) $pathObject;
        $directory  = ($this->cache[$path] ?? null)?->get();

        if ($directory !== null && !($directory instanceof Directory)) {
            return null;
        }

        if ($directory instanceof Directory) {
            return $directory;
        }

        // Create
        if (is_dir($path)) {
            $directory          = !$root->getPath()->isEqual($pathObject)
                ? new Directory($pathObject)
                : $root;
            $this->cache[$path] = WeakReference::create($directory);
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
        Directory $root,
        array|string|null $patterns = null,
        array|string|int|null $depth = null,
        array|string|null $exclude = null,
    ): Iterator {
        yield from $this->getIterator($root, $this->getFile(...), $patterns, $depth, $exclude);
    }

    /**
     * @param array<array-key, string>|string|null         $patterns {@see Finder::name()}
     * @param array<array-key, string|int>|string|int|null $depth    {@see Finder::depth()}
     * @param array<array-key, string>|string|null         $exclude  {@see Finder::notPath()}
     *
     * @return Iterator<array-key, Directory>
     */
    public function getDirectoriesIterator(
        Directory $root,
        array|string|null $patterns = null,
        array|string|int|null $depth = null,
        array|string|null $exclude = null,
    ): Iterator {
        yield from $this->getIterator($root, $this->getDirectory(...), $patterns, $depth, $exclude);
    }

    /**
     * @template T of object
     *
     * @param Closure(Directory, SplFileInfo): ?T          $factory
     * @param array<array-key, string>|string|null         $patterns {@see Finder::name()}
     * @param array<array-key, string|int>|string|int|null $depth    {@see Finder::depth()}
     * @param array<array-key, string>|string|null         $exclude  {@see Finder::notPath()}
     *
     * @return Iterator<array-key, T>
     */
    protected function getIterator(
        Directory $root,
        Closure $factory,
        array|string|null $patterns = null,
        array|string|int|null $depth = null,
        array|string|null $exclude = null,
    ): Iterator {
        $finder = Finder::create()
            ->ignoreVCSIgnored(true)
            ->exclude('node_modules')
            ->exclude('vendor')
            ->in((string) $root)
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

        foreach ($finder as $info) {
            $item = $factory($root, $info);

            if ($item !== null) {
                yield $item;
            }
        }

        yield from [];
    }

    public function save(File $file): bool {
        // Modified?
        if (!$file->isModified()) {
            return true;
        }

        // Inside?
        if ($this->output?->isInside($file->getPath()) !== true) {
            return false;
        }

        // Directory?
        $directory = (string) $file->getPath()->getDirectoryPath();

        if (!is_dir($directory) && !mkdir($directory, recursive: true)) {
            return false;
        }

        // Save
        return file_put_contents((string) $file->getPath(), $file->getContent()) !== false;
    }
}
