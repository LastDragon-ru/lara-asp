<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\Stream as StreamContract;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\StreamFactory as StreamFactoryContract;
use LastDragon_ru\LaraASP\GraphQL\Stream\Streams\Database;
use LastDragon_ru\LaraASP\GraphQL\Stream\Streams\Scout;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
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

    /**
     * @inheritDoc
     */
    #[Override]
    public function enhance(
        object $builder,
        mixed $root,
        array $args,
        GraphQLContext $context,
        ResolveInfo $info,
    ): object {
        $builder = $info->enhanceBuilder($builder, [], $root, $args, $context, $info);
        $builder = $builder instanceof Relation
            ? $builder->getQuery()
            : $builder;

        return $builder;
    }

    #[Override]
    public function create(object $builder, string $key, int $limit, Offset $offset): StreamContract {
        return $builder instanceof ScoutBuilder
            ? new Scout($builder, $key, $limit, $offset)
            : new Database($builder, $key, $limit, $offset);
    }
}
