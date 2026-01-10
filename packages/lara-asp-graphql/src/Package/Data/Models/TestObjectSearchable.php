<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use LastDragon_ru\LaraASP\Eloquent\Concerns\WithoutTimestamps;

/**
 * @internal
 *
 * @property string $id
 * @property string $value
 */
class TestObjectSearchable extends Model {
    /**
     * @use HasFactory<TestObjectSearchableFactory>
     */
    use HasFactory;
    use Searchable;
    use WithoutTimestamps;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var ?string
     */
    protected $table = 'test_objects';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var bool
     */
    public $incrementing = false;

    protected static function newFactory(): TestObjectSearchableFactory {
        return TestObjectSearchableFactory::new();
    }
}
