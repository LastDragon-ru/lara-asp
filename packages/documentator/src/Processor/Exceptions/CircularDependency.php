<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function array_map;
use function implode;
use function sprintf;

class CircularDependency extends ProcessorError {
    /**
     * @param list<File> $stack
     */
    public function __construct(
        protected Directory $root,
        protected readonly File $target,
        protected readonly File $dependency,
        protected readonly array $stack,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                <<<'MESSAGE'
                Circular Dependency detected:

                %2$s
                ! %1$s

                (root: `%3$s`)
                MESSAGE,
                $this->dependency->getRelativePath($this->root),
                '* '.implode("\n* ", array_map(fn ($f) => $f->getRelativePath($this->root), $this->stack)),
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

    public function getDependency(): File {
        return $this->dependency;
    }

    /**
     * @return list<File>
     */
    public function getStack(): array {
        return $this->stack;
    }
}
