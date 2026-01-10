<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Contracts;

interface Serializer {
    /**
     * @param array<string, mixed> $context
     */
    public function serialize(object $object, ?string $format = null, array $context = []): string;

    /**
     * @template T of object
     *
     * @param class-string<T>      $object
     * @param array<string, mixed> $context
     *
     * @return T
     */
    public function deserialize(
        string $object,
        string $data,
        ?string $format = null,
        array $context = [],
    ): object;
}
