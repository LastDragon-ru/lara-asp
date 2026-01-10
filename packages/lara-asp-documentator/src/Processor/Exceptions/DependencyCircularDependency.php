<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Throwable;

use function implode;
use function sprintf;

class DependencyCircularDependency extends DependencyError {
    /**
     * @param list<FilePath> $stack
     */
    public function __construct(
        protected readonly DirectoryPath|FilePath $target,
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
                $this->target,
                '* '.implode("\n* ", $this->stack),
            ),
            $previous,
        );
    }

    public function getTarget(): DirectoryPath|FilePath {
        return $this->target;
    }

    /**
     * @return list<FilePath>
     */
    public function getStack(): array {
        return $this->stack;
    }
}
