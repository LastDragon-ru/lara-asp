<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Exceptions;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\PackageException;
use Throwable;

use function sprintf;

class FailedToSerialize extends PackageException {
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private Serializable $serializable,
        private string $format,
        private array $context,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to serialize `%s` into `%s`.',
                $serializable::class,
                $format,
            ),
            $previous,
        );
    }

    public function getSerializable(): Serializable {
        return $this->serializable;
    }

    public function getFormat(): string {
        return $this->format;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array {
        return $this->context;
    }
}
