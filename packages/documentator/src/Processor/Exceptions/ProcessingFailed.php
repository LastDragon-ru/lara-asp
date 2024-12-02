<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Core\Path\Path;
use Throwable;

use function sprintf;

class ProcessingFailed extends ProcessorError {
    public function __construct(
        protected Path $path,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Processing failed (path: `%s`)',
                $this->path,
            ),
            $previous,
        );
    }

    public function getPath(): Path {
        return $this->path;
    }
}
