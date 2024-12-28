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
use Symfony\Component\Finder\Finder;

use function is_string;

/**
 * @see Finder
 * @implements Dependency<Iterator<mixed, Directory>>
 */
class DirectoryIterator implements Dependency {
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
    public function __invoke(FileSystem $fs): mixed {
        try {
            yield from $fs->getDirectoriesIterator($this->directory, $this->patterns, $this->depth, $this->exclude);
        } catch (DirectoryNotFound $exception) {
            throw new DependencyUnresolvable($this, $exception);
        }
    }

    #[Override]
    public function getPath(): DirectoryPath {
        return match (true) {
            $this->directory instanceof Directory => $this->directory->getPath(),
            is_string($this->directory)           => new DirectoryPath($this->directory),
            default                               => $this->directory,
        };
    }
}
