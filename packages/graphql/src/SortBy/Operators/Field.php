<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\EloquentSorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\QuerySorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\ScoutSorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\Sorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Direction as DirectionType;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

use function is_a;

class Field extends BaseOperator {
    /**
     * @var array<class-string, class-string<Sorter<*>>>
     */
    private array $sorters = [
        EloquentBuilder::class => EloquentSorter::class,
        QueryBuilder::class    => QuerySorter::class,
        ScoutBuilder::class    => ScoutSorter::class,
    ];

    #[Override]
    public static function getName(): string {
        return 'field';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return $provider->getType(DirectionType::class, $source);
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Property clause.';
    }

    #[Override]
    public function isBuilderSupported(string $builder): bool {
        return $this->getSorterClass($builder) !== null;
    }

    #[Override]
    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        $direction = Cast::to(Direction::class, $argument->value);
        $sorter    = $this->getSorter($builder);
        $nulls     = null;

        if ($sorter) {
            $sorter->sort($builder, $property, $direction, $nulls);
        } else {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        return $builder;
    }

    /**
     * @return ?Sorter<object>
     */
    protected function getSorter(object $builder): ?Sorter {
        $sorter = $this->getSorterClass($builder::class);
        $sorter = $sorter
            ? Container::getInstance()->make($sorter)
            : null;

        return $sorter;
    }

    /**
     * @param class-string $builder
     *
     * @return ?class-string
     */
    protected function getSorterClass(string $builder): ?string {
        $class = null;

        foreach ($this->sorters as $builderClass => $sorterClass) {
            if (is_a($builder, $builderClass, true)) {
                $class = $sorterClass;
                break;
            }
        }

        return $class;
    }
}
