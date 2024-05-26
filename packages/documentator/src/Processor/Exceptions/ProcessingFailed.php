<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use Throwable;

use function sprintf;

class ProcessingFailed extends ProcessorError {
    public function __construct(
        protected Directory $root,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Processing failed (root: `%s`)',
                $this->root->getPath(),
            ),
            $previous,
        );
    }

    public function getRoot(): Directory {
        return $this->root;
    }
}
