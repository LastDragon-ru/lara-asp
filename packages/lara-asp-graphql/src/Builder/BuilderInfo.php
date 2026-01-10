<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;

use function is_a;

class BuilderInfo {
    /**
     * @param class-string $builder
     */
    public function __construct(
        protected string $name,
        protected string $builder,
    ) {
        // empty
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @return class-string
     */
    public function getBuilder(): string {
        return $this->builder;
    }

    /**
     * @param class-string $type
     */
    public static function create(string $type): ?self {
        return match (true) {
            is_a($type, EloquentBuilder::class, true),
            is_a($type, EloquentModel::class, true),
            is_a($type, EloquentRelation::class, true) => new self('', EloquentBuilder::class),
            is_a($type, ScoutBuilder::class, true)     => new self('Scout', ScoutBuilder::class),
            is_a($type, QueryBuilder::class, true)     => new self('Query', QueryBuilder::class),
            is_a($type, Collection::class, true)       => new self('Collection', Collection::class),
            default                                    => null,
        };
    }
}
