<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts;

/**
 * @template TBuilder of object
 */
interface SorterFactory {
    /**
     * @phpstan-assert-if-true ($builder is object? TBuilder : class-string<TBuilder>) $builder
     *
     * @param object|class-string                                                      $builder
     */
    public function isSupported(object|string $builder): bool;

    /**
     * @param TBuilder|class-string<TBuilder> $builder
     *
     * @return ?Sorter<TBuilder>
     */
    public function create(object|string $builder): ?Sorter;
}
