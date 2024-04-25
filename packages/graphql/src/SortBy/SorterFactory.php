<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\BuilderHelperFactory;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Sorter as SorterContract;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\SorterFactory as SorterFactoryContract;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\EloquentSorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\QuerySorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\ScoutSorter;
use Override;

use function assert;

/**
 * @template TBuilder of object
 *
 * @implements SorterFactoryContract<TBuilder>
 */
class SorterFactory implements SorterFactoryContract {
    use BuilderHelperFactory;

    public function __construct(
        protected readonly ContainerResolver $container,
    ) {
        $this->addHelper(EloquentBuilder::class, EloquentSorter::class);
        $this->addHelper(QueryBuilder::class, QuerySorter::class);
        $this->addHelper(ScoutBuilder::class, ScoutSorter::class);
    }

    #[Override]
    public function isSupported(string|object $builder): bool {
        return $this->getHelperClass($builder) !== null;
    }

    #[Override]
    public function create(object|string $builder): ?SorterContract {
        $helper = $this->getHelper($builder);

        assert($helper instanceof SorterContract);

        return $helper;
    }

    #[Override]
    private function getContainer(): Container {
        return $this->container->getInstance();
    }
}
