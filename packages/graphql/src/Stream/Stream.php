<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\Stream as StreamContract;

/**
 * @template TBuilder of EloquentBuilder<EloquentModel>|QueryBuilder|ScoutBuilder
 *
 * @implements StreamContract<TBuilder>
 */
class Stream implements StreamContract {
    /**
     * @param TBuilder    $builder
     * @param int<1, max> $limit
     */
    public function __construct(
        protected readonly object $builder,
        protected readonly string $key,
        protected readonly Cursor $cursor,
        protected readonly int $limit,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    public function getItems(): iterable {
        return [];  // fixme(graphql)!: Not implemented.
    }

    public function getLength(): ?int {
        return 0; // fixme(graphql)!: Not implemented.
    }

    public function getNextCursor(): ?Cursor {
        return null; // fixme(graphql)!: Not implemented.
    }

    public function getCurrentCursor(): Cursor {
        return $this->cursor;
    }

    public function getPreviousCursor(): ?Cursor {
        return null; // fixme(graphql)!: Not implemented.
    }
}
