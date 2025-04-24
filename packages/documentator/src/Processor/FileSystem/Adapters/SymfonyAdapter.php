<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Globs;
use Override;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function is_dir;
use function is_file;

class SymfonyAdapter implements Adapter {
    protected readonly Filesystem $filesystem;

    public function __construct() {
        $this->filesystem = new Filesystem();
    }

    #[Override]
    public function isFile(FilePath|DirectoryPath|string $path): bool {
        return is_file((string) $path);
    }

    #[Override]
    public function isDirectory(FilePath|DirectoryPath|string $path): bool {
        return is_dir((string) $path);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getFilesIterator(
        string $directory,
        array|string|null $include = null,
        array|string|null $exclude = null,
        ?int $depth = null,
    ): iterable {
        foreach ($this->getFinder($directory, $include, $exclude, $depth)->files() as $file) {
            yield $file->getPathname();
        }

        yield from [];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getDirectoriesIterator(
        string $directory,
        array|string|null $include = null,
        array|string|null $exclude = null,
        ?int $depth = null,
    ): iterable {
        foreach ($this->getFinder($directory, $include, $exclude, $depth)->directories() as $file) {
            yield $file->getPathname();
        }

        yield from [];
    }

    #[Override]
    public function read(string $path): string {
        return $this->filesystem->readFile($path);
    }

    #[Override]
    public function write(string $path, string $content): void {
        $this->filesystem->dumpFile($path, $content);
    }

    /**
     * @param array<array-key, string>|string|null $exclude
     * @param array<array-key, string>|string|null $include
     */
    protected function getFinder(
        string $directory,
        array|string|null $include = null,
        array|string|null $exclude = null,
        ?int $depth = null,
    ): Finder {
        $finder = Finder::create()
            ->ignoreVCSIgnored(true)
            ->exclude('node_modules')
            ->exclude('vendor')
            ->in($directory);

        if ($depth !== null) {
            $finder = $finder->depth("<= {$depth}");
        }

        if ($include !== null) {
            $finder = $finder->name($include);
        }

        if ($exclude !== null) {
            $exclude = (new Globs((array) $exclude))->regexp;

            if ($exclude !== null) {
                $finder = $finder->notPath($exclude);
            }
        }

        return $finder;
    }
}
