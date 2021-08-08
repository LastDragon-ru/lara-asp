<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Exceptions;

use LastDragon_ru\LaraASP\Formatter\PackageException;
use NumberFormatter;
use Throwable;

use function sprintf;

class FailedToCreateNumberFormatter extends PackageException {
    public function __construct(
        protected string $type,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Failed to create instance of `%s` for type `%s`.',
            NumberFormatter::class,
            $this->getType(),
        ), $previous);
    }

    public function getType(): string {
        return $this->type;
    }
}
