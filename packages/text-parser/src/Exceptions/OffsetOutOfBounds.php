<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Exceptions;

use LastDragon_ru\TextParser\PackageException;
use Throwable;

use function sprintf;

class OffsetOutOfBounds extends PackageException {
    public function __construct(
        protected ?int $offset,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Offset `%s` is out of bounds.',
                $this->offset,
            ),
            $previous,
        );
    }
}
