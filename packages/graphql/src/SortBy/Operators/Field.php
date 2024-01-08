<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Sorter;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\SorterFactory;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Direction as DirectionType;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

use function config;
use function is_array;

class Field extends BaseOperator {
    /**
     * @param SorterFactory<object> $factory
     */
    public function __construct(
        protected readonly SorterFactory $factory,
    ) {
        parent::__construct();
    }

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
        return $this->factory->isSupported($builder);
    }

    #[Override]
    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        $sorter = $this->factory->create($builder);

        if ($sorter) {
            $direction = $argument->value instanceof Direction ? $argument->value : Direction::Asc;
            $nulls     = $this->getNulls($sorter, $property, $direction);

            $sorter->sort($builder, $property, $direction, $nulls);
        } else {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        return $builder;
    }

    /**
     * @param Sorter<object> $sorter
     */
    protected function getNulls(Sorter $sorter, Property $property, Direction $direction): ?Nulls {
        // Sortable?
        if (!$sorter->isNullsSortable()) {
            return null;
        }

        // Default
        $nulls     = null;
        $config    = config(Package::Name.'.sort_by.nulls');
        $direction = match ($direction) {
            Direction::asc  => Direction::Asc,
            Direction::desc => Direction::Desc,
            default         => $direction,
        };

        if (is_array($config) && isset($config[$direction->value])) {
            $config = $config[$direction->value];
        }

        if ($config instanceof Nulls) {
            $nulls = $config;
        }

        return $nulls;
    }
}
