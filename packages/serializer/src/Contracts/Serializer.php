<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Contracts;

interface Serializer {
    /**
     * @param array<string, mixed> $context
     */
    public function serialize(Serializable $serializable, string $format = null, array $context = []): string;

    /**
     * @template T of Serializable
     *
     * @param class-string<T>      $serializable
     * @param array<string, mixed> $context
     *
     * @return T
     */
    public function deserialize(
        string $serializable,
        string $data,
        string $format = null,
        array $context = [],
    ): Serializable;
}
