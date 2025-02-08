<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Exceptions;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use Throwable;

use function sprintf;

class MetadataUnserializable extends MetadataError {
    public function __construct(
        protected readonly FilePath $target,
        protected readonly object $metadata,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to resolve `%s` metadata for `%s` file.',
                $this->metadata::class,
                $this->target,
            ),
            $previous,
        );
    }

    public function getTarget(): FilePath {
        return $this->target;
    }

    public function getMetadata(): object {
        return $this->metadata;
    }
}
