<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Eloquent\Concerns\WithoutTimestamps;

/**
 * @internal
 */
class TestObject extends Model {
    /**
     * @use HasFactory<TestObjectFactory>
     */
    use HasFactory;
    use WithoutTimestamps;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $table = 'test_objects';

    protected static function newFactory(): TestObjectFactory {
        return TestObjectFactory::new();
    }
}
