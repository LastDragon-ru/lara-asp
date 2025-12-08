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
        ?int $depth = null,
    ): iterable {
        $map      = new SymfonyPathMap();
        $include  = $include !== [] ? (new SymfonyGlob($map, $include))->match(...) : null;
        $exclude  = $exclude !== [] ? (new SymfonyGlob($map, $exclude))->mismatch(...) : null;
        $iterator = $this->getFinder($directory, $include, $exclude, $depth)->files();

        foreach ($iterator as $file) {
            $path = $map->get($file);

            if ($path instanceof FilePath) {
                yield $path;
            }
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
    public function reset(): void {
        // empty
    }

    /**
     * @param Closure(SplFileInfo): bool|null $include
     * @param Closure(SplFileInfo): bool|null $exclude
     * @param ?int<0, max>                    $depth
     */
    protected function getFinder(
        DirectoryPath $directory,
        ?Closure $include = null,
        ?Closure $exclude = null,
        ?int $depth = null,
    ): Finder {
        $finder = Finder::create()
            ->ignoreVCSIgnored(true)
            ->exclude('node_modules')
            ->exclude('vendor-bin')
            ->exclude('vendor')
            ->in((string) $directory);

        if ($depth !== null) {
            $finder = $finder->depth("<= {$depth}");
        }

        if ($include !== null) {
            $finder = $finder->filter($include);
        }

        if ($exclude !== null) {
            $finder = $finder->filter($exclude, true);
        }

        return $finder;
    }
}
