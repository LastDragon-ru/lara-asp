<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use Traversable;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Mixins\EloquentBuilderMixin
 */
class EloquentBuilderMixinTest extends TestCase {
    /**
     * @covers ::getDefaultKeyName
     */
    public function testGetDefaultKeyName(): void {
        $model = new class() extends Model {
            protected $primaryKey = 'idddd';
        };

        $this->assertTrue(Builder::hasGlobalMacro('getDefaultKeyName'));
        $this->assertEquals('idddd', $model->query()->getDefaultKeyName());
    }

    /**
     * @covers ::iterator
     */
    public function testIterator(): void {
        $model = new class() extends Model {
            // empty
        };

        $this->assertTrue(Builder::hasGlobalMacro('iterator'));
        $this->assertInstanceOf(Traversable::class, $model->query()->iterator());
    }
}
