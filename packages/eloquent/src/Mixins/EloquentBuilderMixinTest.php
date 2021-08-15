<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedChangeSafeIterator;
use LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedIterator;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\WithTestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;

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
     * @covers ::getChunkedIterator
     */
    public function testIterator(): void {
        $actual = null;
        $model  = new class() extends Model {
            // empty
        };

        $this->assertTrue(Builder::hasGlobalMacro('getChunkedIterator'));

        try {
            $actual = $model->query()->getChunkedIterator();
        } catch (Exception) {
            // empty
        }

        $this->assertInstanceOf(ChunkedIterator::class, $actual);
    }

    /**
     * @covers ::getChangeSafeIterator
     */
    public function testChangeSafeIterator(): void {
        $actual = null;
        $model  = new class() extends Model {
            // empty
        };

        $this->assertTrue(Builder::hasGlobalMacro('getChangeSafeIterator'));

        try {
            $actual = $model->query()->getChangeSafeIterator();
        } catch (Exception) {
            // empty
        }

        $this->assertInstanceOf(ChunkedChangeSafeIterator::class, $actual);
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
