<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function sprintf;

class FileSaveFailed extends ProcessorError {
    public function __construct(
        protected readonly File $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to save `%s` file.',
                $this->target,
            ),
            $previous,
        );
    }

    public function getTarget(): File {
        return $this->target;
    }
}
