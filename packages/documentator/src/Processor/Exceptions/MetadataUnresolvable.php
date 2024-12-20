<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Throwable;

use function sprintf;

class MetadataUnresolvable extends MetadataError {
    public function __construct(
        protected readonly FileSystem $filesystem,
        protected readonly File $target,
        /**
         * @var Metadata<*>
         */
        protected readonly Metadata $metadata,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to resolve `%s` metadata for `%s` file.',
                $this->metadata::class,
                $this->filesystem->getPathname($this->target),
            ),
            $previous,
        );
    }

    public function getFilesystem(): FileSystem {
        return $this->filesystem;
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
