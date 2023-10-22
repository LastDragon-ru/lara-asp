<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Streams;

use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\Stream as StreamContract;
use LastDragon_ru\LaraASP\GraphQL\Stream\Offset;

/**
 * @template TBuilder of object
 */
abstract class Stream implements StreamContract {
    /**
     * @param TBuilder    $builder
     * @param int<1, max> $limit
     */
    public function __construct(
        protected readonly object $builder,
        protected readonly string $key,
        protected readonly int $limit,
        protected readonly Offset $offset,
    ) {
        // empty
    }

    public function getCurrentOffset(): Offset {
        return $this->offset;
    }
}
