<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function sprintf;

class FileMetadataFailed extends MetadataError {
    public function __construct(
        protected Directory $root,
        protected readonly File $target,
        /**
         * @var Metadata<*>
         */
        protected readonly Metadata $metadata,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to retrieve `%s` metadata for `%s` file (root: `%s`).',
                $this->metadata::class,
                $this->root->getRelativePath($this->target),
                $this->root->getPath(),
            ),
            $previous,
        );
    }

    public function getRoot(): Directory {
        return $this->root;
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
