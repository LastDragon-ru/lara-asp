<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns\Model;

/**
 * @template TModel of Model
 *
 * @extends Relation<TModel>
 */
class Unsupported extends Relation {
    public function __construct() {
        $model = new class('models') extends Model {
            // empty
        };

        parent::__construct(
            $model->newQuery(),
            $model,
        );
    }

    public function addConstraints(): void {
        // empty
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $models
     */
    public function addEagerConstraints(array $models): void {
        // empty
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $models
     *
     * @return array<mixed>
     */
    public function initRelation(array $models, $relation): array {
        return [];
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed>           $models
     * @param Collection<int, Model> $results
     *
     * @return array<mixed>
     */
    public function match(array $models, Collection $results, $relation): array {
        return [];
    }

    public function getResults(): mixed {
        return null;
    }
}
