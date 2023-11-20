<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns\Model;
use Override;

/**
 * @template TModel of Model
 *
 * @extends Relation<TModel>
 *
 * @internal
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

    #[Override]
    public function addConstraints(): void {
        // empty
    }

    /**
     * @inheritDoc
     *
     * @param array<array-key, mixed> $models
     */
    #[Override]
    public function addEagerConstraints(array $models): void {
        // empty
    }

    /**
     * @inheritDoc
     *
     * @param array<array-key, mixed> $models
     *
     * @return array<array-key, mixed>
     */
    #[Override]
    public function initRelation(array $models, $relation): array {
        return [];
    }

    /**
     * @inheritDoc
     *
     * @param array<array-key, mixed> $models
     * @param Collection<int, Model>  $results
     *
     * @return array<array-key, mixed>
     */
    #[Override]
    public function match(array $models, Collection $results, $relation): array {
        return [];
    }

    #[Override]
    public function getResults(): mixed {
        return null;
    }
}
