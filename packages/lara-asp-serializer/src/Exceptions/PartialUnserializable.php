<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Exceptions;

use LastDragon_ru\LaraASP\Serializer\PackageException;
use Throwable;

class PartialUnserializable extends PackageException {
    public function __construct(?Throwable $previous = null) {
        parent::__construct(
            'Partial object cannot be serialized.',
            $previous,
        );
    }
}
