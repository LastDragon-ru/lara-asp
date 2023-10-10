<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\Stream as StreamContract;

/**
 * @implements StreamContract<EloquentBuilder<EloquentModel>|QueryBuilder|ScoutBuilder>
 */
class Stream implements StreamContract {
    /**
     * @param int<1, max> $chunk
     */
    public function __construct(
        protected readonly object $builder,
        protected readonly string $key,
        protected readonly Cursor $cursor,
        protected readonly int $chunk,
    ) {
        // empty
    }

    public function count(): int {
        return 0; // fixme(graphql)!: Not implemented.
    }
}
