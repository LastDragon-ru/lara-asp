<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Testing\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Eloquent\Concerns\WithoutTimestamps;

/**
 * @internal
 */
class TestObject extends Model {
    use HasFactory;
    use WithoutTimestamps;

    protected $table = 'test_objects';

    protected static function newFactory(): Factory {
        return TestObjectFactory::new();
    }
}
