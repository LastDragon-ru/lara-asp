<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Exceptions;

use LastDragon_ru\LaraASP\Serializer\PackageException;
use Throwable;

use function gettype;
use function is_object;
use function sprintf;

class FailedToCast extends PackageException {
    /**
     * @param class-string $target
     */
    public function __construct(
        private string $target,
        private mixed $value,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to cast `%2$s` to `%1$s`.',
                $this->target,
                is_object($this->value)
                    ? $this->value::class
                    : gettype($value),
            ),
            $previous,
        );
    }

    /**
     * @return class-string
     */
    public function getTarget(): string {
        return $this->target;
    }

    public function getValue(): mixed {
        return $this->value;
    }
}
