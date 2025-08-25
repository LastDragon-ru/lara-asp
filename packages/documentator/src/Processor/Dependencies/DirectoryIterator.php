<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use Iterator;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DirectoryNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;

use function is_string;

/**
 * @implements Dependency<Iterator<mixed, Directory>>
 */
readonly class DirectoryIterator implements Dependency {
    public function __construct(
        protected Directory|DirectoryPath|string $directory,
        /**
         * Glob(s) to include.
         *
         * @var list<string>
         */
        protected array $include = [],
        /**
         * Glob(s) to exclude.
         *
         * @var list<string>
         */
        protected array $exclude = [],
        /**
         * Maximum depth.
         */
        protected ?int $depth = null,
    ) {
        // empty
    }

    /**
     * @return Iterator<mixed, Directory>
     */
    #[Override]
    public function __invoke(FileSystem $fs): Iterator {
        try {
            yield from $fs->getDirectoriesIterator($this->directory, $this->include, $this->exclude, $this->depth);
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
