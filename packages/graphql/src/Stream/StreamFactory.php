<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\BuilderUnsupported;
use Nuwave\Lighthouse\Execution\ResolveInfo;

use function is_a;

class StreamFactory {
    public function __construct() {
        // empty
    }

    /**
     * @phpstan-assert-if-true (
     *      $builder is object
     *          ? EloquentBuilder<Model>|QueryBuilder|ScoutBuilder
     *          : class-string<EloquentBuilder<Model>|QueryBuilder|ScoutBuilder>
     *      )                     $builder
     *
     * @param object|class-string $builder
     */
    public function isBuilderSupported(object|string $builder): bool {
        return is_a($builder, EloquentBuilder::class, true)
            || is_a($builder, QueryBuilder::class, true)
            || is_a($builder, ScoutBuilder::class, true);
    }

    /**
     * @param int<1, max> $chunk
     */
    public function create(
        ObjectFieldSource $source,
        ResolveInfo $info,
        object $builder,
        string $key,
        Cursor $cursor,
        int $chunk,
    ): Stream {
        if (!$this->isBuilderSupported($builder)) {
            throw new BuilderUnsupported($source, $builder::class);
        }

        return new Stream($info, $builder, $key, $cursor, $chunk);
    }
}
