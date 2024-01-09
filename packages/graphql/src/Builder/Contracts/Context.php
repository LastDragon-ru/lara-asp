<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

interface Context {
    /**
     * @template T of object
     *
     * @param class-string<T> $key
     *
     * @return T|null
     */
    public function get(string $key): mixed;

    /**
     * @template T of object
     *
     * @param array<class-string<T>, T|null> $context
     */
    public function override(array $context): static;
}
