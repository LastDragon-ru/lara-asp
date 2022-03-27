<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns\Model;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Relations\Unsupported;

/**
 * @internal
 *
 * @property string                 $id
 * @property string                 $name
 * @property string                 $organization_id
 * @property DateTimeInterface|null $deleted_at
 */
class User extends Model {
    public const Id = 'b56ce2b1-8af9-4751-8fad-8485930c9c40';

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct('users', self::Id, $attributes);
    }

    /**
     * @return BelongsTo<Organization, User>
     */
    public function organization(): BelongsTo {
        return $this
            ->belongsTo(Organization::class, 'foreignKey', 'ownerKey');
    }

    /**
     * @return HasOne<Car>
     */
    public function car(): HasOne {
        return $this
            ->hasOne(Car::class, 'foreignKey', 'localKey')
            ->where('favorite', '=', 1);
    }

    /**
     * @return HasMany<Car>
     */
    public function cars(): HasMany {
        return $this
            ->hasMany(Car::class, 'foreignKey', 'localKey')
            ->whereNull('deleted_at');
    }

    /**
     * @return HasOneThrough<Role>
     */
    public function role(): HasOneThrough {
        return $this
            ->hasOneThrough(
                Role::class,
                UserRole::class,
                'firstKey',
                'secondKey',
                'localKey',
                'secondLocalKey',
            )
            ->whereNull('deleted_at');
    }

    /**
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany {
        return $this
            ->belongsToMany(
                Role::class,
                (new UserRole())->getTable(),
                'foreignPivotKey',
                'relatedPivotKey',
                'parentKey',
                'relatedKey',
            )
            ->whereNull('deleted_at');
    }

    /**
     * @return MorphOne<Image>
     */
    public function avatar(): MorphOne {
        return $this
            ->morphOne(Image::class, 'imageable', null, null, 'localKey')
            ->whereNull('deleted_at');
    }

    /**
     * @return MorphToMany<Tag>
     */
    public function tags(): MorphToMany {
        return $this->morphToMany(
            Tag::class,
            'taggable',
            null,
            'foreignPivotKey',
            'relatedPivotKey',
            'parentKey',
            'relatedKey',
        );
    }

    /**
     * @return Unsupported<Model>
     */
    public function unsupported(): Unsupported {
        return new Unsupported();
    }
}
