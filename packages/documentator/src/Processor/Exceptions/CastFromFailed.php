<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\Path\FilePath;
use Throwable;

use function sprintf;

class CastFromFailed extends CastError {
    public function __construct(
        protected readonly FilePath $target,
        protected readonly object $object,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Cast from `%s` failed for `%s` file.',
                $this->object::class,
                $this->target,
            ),
            $previous,
        );
    }

    public function getTarget(): FilePath {
        return $this->target;
    }

    public function getObject(): object {
        return $this->object;
    }
}
