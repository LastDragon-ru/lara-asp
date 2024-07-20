<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function sprintf;

class DependencyNotFound extends ProcessorError {
    public function __construct(
        protected Directory $root,
        protected readonly File $target,
        /**
         * @var Dependency<*>
         */
        protected readonly Dependency $dependency,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Dependency `%s` of `%s` not found (root: `%s`).',
                Path::getRelativePath($this->root->getPath(), (string) $this->dependency),
                $this->target->getRelativePath($this->root),
                $this->root->getPath(),
            ),
            $previous,
        );
    }

    public function getRoot(): Directory {
        return $this->root;
    }

    public function getTarget(): File {
        return $this->target;
    }

    /**
     * @return Dependency<*>
     */
    public function getDependency(): Dependency {
        return $this->dependency;
    }
}
