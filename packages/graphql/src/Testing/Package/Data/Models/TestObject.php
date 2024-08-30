<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Eloquent\Concerns\WithoutTimestamps;

/**
 * @internal
 *
 * @property string $id
 * @property string $value
 */
class TestObject extends Model {
    /**
     * @use HasFactory<TestObjectFactory>
     */
    use HasFactory;
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

    protected static function newFactory(): TestObjectFactory {
        return TestObjectFactory::new();
    }
}
