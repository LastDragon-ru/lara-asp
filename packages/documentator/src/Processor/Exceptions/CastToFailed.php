<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function sprintf;

class CastToFailed extends CastError {
    public function __construct(
        protected readonly File $target,
        /**
         * @var class-string
         */
        protected readonly string $class,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Cast to `%s` failed for `%s` file.',
                $this->class,
                $this->target,
            ),
            $previous,
        );
    }

    public function getTarget(): File {
        return $this->target;
    }

    /**
     * @return class-string
     */
    public function getClass(): string {
        return $this->class;
    }
}
