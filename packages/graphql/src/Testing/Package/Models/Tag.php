<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns\Model;

/**
 * @internal
 *
 * @property string $id
 * @property string $name
 */
class Tag extends Model {
    public const Id = 'c0e75510-d91e-49b6-85e5-0146dfb3a630';

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct('tags', self::Id, $attributes);
    }

    /**
     * @return MorphToMany<User>
     */
    public function users(): MorphToMany {
        return $this->morphedByMany(
            User::class,
            'taggable',
            null,
            'foreignPivotKey',
            'relatedPivotKey',
            'parentKey',
            'relatedKey',
        );
    }
}
