<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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
    public const string Id = 'e3d1c188-6a25-4994-94f7-98e0a44d0607';

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct('images', self::Id, $attributes);
    }

    /**
     * @return MorphTo<EloquentModel, covariant self>
     */
    public function imageable(): MorphTo {
        return $this->morphTo();
    }
}
