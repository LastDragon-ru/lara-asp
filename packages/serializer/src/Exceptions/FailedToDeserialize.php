<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Exceptions;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\PackageException;
use Throwable;

use function sprintf;

class FailedToDeserialize extends PackageException {
    /**
     * @param class-string<Serializable> $serializable
     * @param array<string, mixed>       $context
     */
    public function __construct(
        private string $serializable,
        private string $data,
        private string $format,
        private array $context,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Failed to deserialize `%s` from `%s`.',
                $serializable,
                $format,
            ),
            $previous,
        );
    }

    /**
     * @return class-string<Serializable>
     */
    public function getSerializable(): string {
        return $this->serializable;
    }

    public function getData(): string {
        return $this->data;
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
