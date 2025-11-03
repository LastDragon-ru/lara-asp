<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use Throwable;

use function sprintf;

class FileNotWritable extends FileSystemError {
    public function __construct(
        protected readonly FilePath $target,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'File `%s` is not writable.',
                $this->target,
            ),
            $previous,
        );
    }

    public function getTarget(): FilePath {
        return $this->target;
    }
}
