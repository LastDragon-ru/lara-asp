<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models\Concerns\Model;

use function array_merge;

/**
 * @internal
 *
 * @property string                 $id
 * @property string                 $name
 * @property string                 $user_id
 * @property int                    $favorite
 * @property DateTimeInterface|null $deleted_at
 */
class Car extends Model {
    public const Id = 'b56ce2b1-8af9-4751-8fad-8485930c9c40';

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct(
            'cars',
            self::Id,
            array_merge($attributes, [
                'user_id' => User::Id,
            ]),
        );
    }

    /**
     * @return BelongsTo<User, Car>
     */
    public function user(): BelongsTo {
        return $this
            ->belongsTo(User::class, 'foreignKey', 'ownerKey')
            ->whereNull('deleted_at');
    }

    /**
     * @return HasOne<CarEngine>
     */
    public function engine(): HasOne {
        return $this
            ->hasOne(CarEngine::class, 'foreignKey', 'localKey')
            ->where('installed', '=', 1);
    }

    /**
     * @return HasMany<CarEngine>
     */
    public function engines(): HasMany {
        return $this
            ->hasMany(CarEngine::class, 'foreignKey', 'localKey');
    }
}
