<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function sprintf;

class FileDependencyNotFound extends ProcessorError {
    public function __construct(
        protected Directory $root,
        protected readonly File $target,
        protected readonly string $path,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Dependency `%s` of `%s` not found (root: `%s`).',
                Path::getRelativePath($this->root->getPath(), $this->path),
                $this->target->getRelativePath($this->root),
                $this->root->getPath(),
            ),
            $previous,
        );
    }

    public function getRoot(): Directory {
        return $this->root;
    }

    public function getPath(): string {
        return $this->path;
    }
}
