<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

/**
 * @internal
 */
class FileMetadataUnresolvable extends FileSystemError {
    public function __construct(
        protected readonly File $target,
        /**
         * @var class-string<Metadata<*>>
         */
        protected readonly string $metadata,
        Throwable $previous,
    ) {
        parent::__construct('@internal', $previous);
    }

    public function getTarget(): File {
        return $this->target;
    }

    /**
     * @return class-string<Metadata<*>>
     */
    public function getMetadata(): string {
        return $this->metadata;
    }
}
