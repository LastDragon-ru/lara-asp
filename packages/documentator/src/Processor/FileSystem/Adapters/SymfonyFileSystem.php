<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Adapters;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Glob;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function is_dir;
use function is_file;

class SymfonyFileSystem implements Adapter {
    protected readonly Filesystem $filesystem;

    public function __construct() {
        $this->filesystem = new Filesystem();
    }

    #[Override]
    public function isFile(FilePath $path): bool {
        return is_file((string) $path);
    }

    #[Override]
    public function isDirectory(DirectoryPath $path): bool {
        return is_dir((string) $path);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getFilesIterator(
        DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
        ?int $depth = null,
    ): iterable {
        foreach ($this->getFinder($directory, $include, $exclude, $depth)->files() as $file) {
            $pathname = $file->getPathname();

            if ($pathname !== '') {
                yield new FilePath($pathname);
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
     * @param list<string> $include
     * @param list<string> $exclude
     */
    protected function getFinder(
        DirectoryPath $directory,
        array $include = [],
        array $exclude = [],
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

        if ($include !== []) {
            $finder = $finder->filter(
                (new Glob($directory, $include))->match(...),
            );
        }

        if ($exclude !== []) {
            $finder = $finder->filter(
                (new Glob($directory, $exclude))->mismatch(...),
                true,
            );
        }

        return $finder;
    }
}
