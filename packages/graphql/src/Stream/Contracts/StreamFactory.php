<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Stream\Offset;

/**
 * @template TBuilder of object
 */
interface StreamFactory {
    /**
     * @phpstan-assert-if-true ($builder is object? TBuilder : class-string<TBuilder>) $builder
     *
     * @param object|class-string                                                      $builder
     */
    public function isSupported(object|string $builder): bool;

    /**
     * @param TBuilder    $builder
     * @param int<1, max> $limit
     */
    public function create(
        object $builder,
        string $key,
        int $limit,
        Offset $offset,
    ): Stream;
}
