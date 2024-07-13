<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;
use SplFileInfo;

use function dirname;

/**
 * @implements Dependency<File>
 */
class FileReference implements Dependency {
    public function __construct(
        public readonly SplFileInfo|File|string $path,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(Directory $root, File $file): mixed {
        $path       = (string) $this;
        $directory  = dirname($file->getPath());
        $dependency = $root->getFile(Path::getPath($directory, $path));

        if ($dependency === null) {
            throw new DependencyNotFound($root, $file, $this);
        }

        return $dependency;
    }

    #[Override]
    public function __toString(): string {
        return match (true) {
            $this->path instanceof SplFileInfo => $this->path->getPathname(),
            $this->path instanceof File        => $this->path->getPath(),
            default                            => $this->path,
        };
    }
}
