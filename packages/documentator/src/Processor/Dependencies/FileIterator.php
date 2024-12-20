<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use Iterator;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;
use Symfony\Component\Finder\Finder;

/**
 * @see Finder
 * @implements Dependency<Iterator<mixed, File>>
 */
class FileIterator implements Dependency {
    public function __construct(
        protected readonly Directory|DirectoryPath|string $directory,
        /**
         * @var array<array-key, string>|string|null {@see Finder::name()}
         */
        protected readonly array|string|null $patterns = null,
        /**
         * @var array<array-key, string|int>|string|int|null {@see Finder::depth()}
         */
        protected readonly array|string|int|null $depth = null,
        /**
         * @var array<array-key, string>|string|null {@see Finder::notPath()}
         */
        protected readonly array|string|null $exclude = null,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs, Directory $root, File $file): mixed {
        // Directory
        $directory = $this->directory;

        if (!($directory instanceof Directory)) {
            $directory = $fs->getDirectory($root, $file->getPath()->getDirectoryPath((string) $this));

            if ($directory === null) {
                throw new DependencyNotFound($root, $file, $this);
            }
        }

        // Resolve
        return $fs->getFilesIterator($directory, $this->patterns, $this->depth, $this->exclude);
    }

    #[Override]
    public function __toString(): string {
        return (string) $this->directory;
    }
}
