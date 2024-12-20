<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Throwable;

use function array_map;
use function implode;
use function sprintf;

class DependencyCircularDependency extends DependencyError {
    /**
     * @param list<File> $stack
     */
    public function __construct(
        protected readonly FileSystem $filesystem,
        protected readonly File $target,
        protected readonly array $stack,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                <<<'MESSAGE'
                Circular Dependency detected:

                %2$s
                ! %1$s
                MESSAGE,
                $this->filesystem->getPathname($this->target),
                '* '.implode("\n* ", array_map(fn ($f) => $this->filesystem->getPathname($f), $this->stack)),
            ),
            $previous,
        );
    }

    public function getFilesystem(): FileSystem {
        return $this->filesystem;
    }

    public function getTarget(): File {
        return $this->target;
    }

    /**
     * @return list<File>
     */
    public function getStack(): array {
        return $this->stack;
    }
}
