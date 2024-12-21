<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function implode;
use function sprintf;

class DependencyCircularDependency extends DependencyError {
    /**
     * @param list<File> $stack
     */
    public function __construct(
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
                $this->target,
                '* '.implode("\n* ", $this->stack),
            ),
            $previous,
        );
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
