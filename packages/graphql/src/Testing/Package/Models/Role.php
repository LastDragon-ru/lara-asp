<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Pivot;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns\Model;

/**
 * @internal
 *
 * @property string                 $id
 * @property string                 $name
 * @property DateTimeInterface|null $deleted_at
 */
class Role extends Model {
    public const string Id = '524e3f62-d642-46ff-aaec-280dabdeb4ae';

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct('roles', self::Id, $attributes);
    }

    /**
     * @return MorphOne<Image, covariant Model>
     */
    public function image(): MorphOne {
        return $this
            ->morphOne(Image::class, 'imageable', null, null, 'localKey');
    }

    /**
     * @return HasOneThrough<User, UserRole, covariant Model>
     */
    public function user(): HasOneThrough {
        return $this
            ->hasOneThrough(
                User::class,
                UserRole::class,
                'firstKey',
                'secondKey',
                'localKey',
                'secondLocalKey',
            );
    }

    /**
     * @return BelongsToMany<User, covariant Model, Pivot>
     */
    public function users(): BelongsToMany {
        return $this
            ->belongsToMany(
                User::class,
                (new UserRole())->getTable(),
                'foreignPivotKey',
                'relatedPivotKey',
                'parentKey',
                'relatedKey',
            )
            ->whereNull('deleted_at');
    }
}
