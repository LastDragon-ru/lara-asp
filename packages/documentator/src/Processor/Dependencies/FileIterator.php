<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use Iterator;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;
use Symfony\Component\Finder\Finder;

use function is_string;

/**
 * @see Finder
 * @implements Dependency<Iterator<mixed, File>>
 */
readonly class FileIterator implements Dependency {
    public function __construct(
        protected Directory|DirectoryPath|string $directory,
        /**
         * Glob(s) to include.
         *
         * @var array<array-key, string>|string|null
         */
        protected array|string|null $include = null,
        /**
         * Glob(s) to exclude.
         *
         * @var array<array-key, string>|string|null
         */
        protected array|string|null $exclude = null,
        /**
         * Maximum depth.
         */
        protected ?int $depth = null,
    ) {
        // empty
    }

    /**
     * @return Iterator<mixed, File>
     */
    #[Override]
    public function __invoke(FileSystem $fs): Iterator {
        try {
            yield from $fs->getFilesIterator($this->directory, $this->include, $this->exclude, $this->depth);
        } catch (DirectoryNotFound $exception) {
            throw new DependencyUnresolvable($exception);
        }
    }

    #[Override]
    public function getPath(FileSystem $fs): Directory|DirectoryPath {
        return is_string($this->directory)
            ? new DirectoryPath($this->directory)
            : $this->directory;
    }
}
