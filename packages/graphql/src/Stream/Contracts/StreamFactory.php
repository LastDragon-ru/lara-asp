<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Stream\Cursor;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

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
     * @param TBuilder             $builder
     * @param array<string, mixed> $args
     *
     * @return TBuilder
     */
    public function enhance(
        object $builder,
        mixed $root,
        array $args,
        GraphQLContext $context,
        ResolveInfo $info,
    ): object;

    /**
     * @param TBuilder    $builder
     * @param int<1, max> $limit
     */
    public function create(
        object $builder,
        string $key,
        Cursor $cursor,
        int $limit,
    ): Stream;
}
