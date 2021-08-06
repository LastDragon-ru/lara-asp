<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\WithTestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use Traversable;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Mixins\EloquentBuilderMixin
 */
class EloquentBuilderMixinTest extends TestCase {
    use WithTestObject;

    /**
     * @covers ::getDefaultKeyName
     */
    public function testGetDefaultKeyName(): void {
        $model = new class() extends Model {
            /**
             * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
             * @var string
             */
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

    /**
     * @covers ::changeSafeIterator
     */
    public function testChangeSafeIterator(): void {
        $model = new class() extends Model {
            // empty
        };

        $this->assertTrue(Builder::hasGlobalMacro('changeSafeIterator'));
        $this->assertInstanceOf(Traversable::class, $model->query()->changeSafeIterator());
    }

    /**
     * @covers ::orderByKey
     * @covers ::orderByKeyDesc
     */
    public function testOrderByKey(): void {
        $a = TestObject::factory()->create();
        $b = TestObject::factory()->create();

        $this->assertEquals([$a, $b], TestObject::query()->orderByKey()->get()->all());
        $this->assertEquals([$b, $a], TestObject::query()->orderByKey('desc')->get()->all());
        $this->assertEquals([$b, $a], TestObject::query()->orderByKeyDesc()->get()->all());
    }
}
