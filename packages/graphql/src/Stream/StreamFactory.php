<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\Stream as StreamContract;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\StreamFactory as StreamFactoryContract;
use LastDragon_ru\LaraASP\GraphQL\Stream\Streams\Database;
use LastDragon_ru\LaraASP\GraphQL\Stream\Streams\Scout;
use Override;

use function is_a;

/**
 * @internal
 * @implements StreamFactoryContract<EloquentBuilder<EloquentModel>|QueryBuilder|ScoutBuilder>
 */
class StreamFactory implements StreamFactoryContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public function isSupported(object|string $builder): bool {
        return is_a($builder, EloquentBuilder::class, true)
            || is_a($builder, QueryBuilder::class, true)
            || is_a($builder, ScoutBuilder::class, true);
    }

    #[Override]
    public function create(object $builder, string $key, int $limit, Offset $offset): StreamContract {
        return $builder instanceof ScoutBuilder
            ? new Scout($builder, $key, $limit, $offset)
            : new Database($builder, $key, $limit, $offset);
    }
}
