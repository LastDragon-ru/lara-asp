<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

/**
 * @internal
 */
class FileMetadataUnresolvable extends MetadataError {
    public function __construct(
        protected readonly File $target,
        /**
         * @var Metadata<*>
         */
        protected readonly Metadata $metadata,
        Throwable $previous,
    ) {
        parent::__construct('', $previous);
    }

    public function getTarget(): File {
        return $this->target;
    }

    /**
     * @return Metadata<*>
     */
    public function getMetadata(): Metadata {
        return $this->metadata;
    }
}
