<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use Closure;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function is_dir;
use function is_file;

class SymfonyFileSystem implements Adapter {
    protected readonly Filesystem $filesystem;

    public function __construct() {
        $this->filesystem = new Filesystem();
    }

    #[Override]
    public function exists(DirectoryPath|FilePath $path): bool {
        return $path instanceof FilePath
            ? is_file((string) $path)
            : is_dir((string) $path);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function search(
        DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        bool $hidden = false,
    ): iterable {
        $map      = new SymfonyPathMap();
        $include  = $include !== [] ? (new SymfonyGlob($map, $include, $hidden))->match(...) : null;
        $exclude  = $exclude !== [] ? (new SymfonyGlob($map, $exclude, $hidden))->mismatch(...) : null;
        $iterator = $this->getFinder($directory, $include, $exclude, $hidden);

        foreach ($iterator as $file) {
            yield $map->get($file);
        }

        yield from [];
    }

    #[Override]
    public function read(FilePath $path): string {
        return $this->filesystem->readFile((string) $path);
    }

    #[Override]
    public function write(FilePath $path, string $content): void {
        $this->filesystem->dumpFile((string) $path, $content);
    }

    #[Override]
    public function delete(DirectoryPath|FilePath $path): void {
        $this->filesystem->remove($path->path);
    }

    /**
     * @param Closure(SplFileInfo): bool|null $exclude
     * @param Closure(SplFileInfo): bool|null $include
     */
    protected function getFinder(
        DirectoryPath $directory,
        ?Closure $include,
        ?Closure $exclude,
        bool $hidden,
    ): Finder {
        $finder = Finder::create()
            ->ignoreVCSIgnored($hidden === false)
            ->ignoreDotFiles($hidden === false)
            ->in((string) $directory);

        if ($include !== null) {
            $finder = $finder->filter($include);
        }

        if ($exclude !== null) {
            $finder = $finder->filter($exclude, true);
        }

        return $finder;
    }
}
