<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function sprintf;

class MetadataUnresolvable extends MetadataError {
    public function __construct(
        protected readonly File $target,
        /**
         * @var class-string
         */
        protected readonly string $metadata,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to resolve `%s` metadata for `%s` file.',
                $this->metadata,
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
    public function getMetadata(): string {
        return $this->metadata;
    }
}
