<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns\Model;

/**
 * @internal
 *
 * @property string                 $id
 * @property string                 $imageable_id
 * @property string                 $imageable_type
 * @property DateTimeInterface|null $deleted_at
 */
class Image extends Model {
    public const Id = 'e3d1c188-6a25-4994-94f7-98e0a44d0607';

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct('images', self::Id, $attributes);
    }

    /**
     * @return MorphTo<User|Role, Image>
     */
    public function imageable(): MorphTo {
        return $this->morphTo();
    }

    /**
     * @return MorphToMany<User>
     */
    public function users(): MorphToMany {
        return $this->morphedByMany(
            User::class,
            'imageable',
            null,
            'foreignPivotKey',
            'relatedPivotKey',
            'parentKey',
            'relatedKey',
        );
    }

    /**
     * @return MorphToMany<Role>
     */
    public function roles(): MorphToMany {
        return $this->morphedByMany(
            Role::class,
            'imageable',
            null,
            'foreignPivotKey',
            'relatedPivotKey',
            'parentKey',
            'relatedKey',
        );
    }
}
