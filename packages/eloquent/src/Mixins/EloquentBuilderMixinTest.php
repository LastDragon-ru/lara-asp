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
 * @covers \LastDragon_ru\LaraASP\Eloquent\Mixins\EloquentBuilderMixin
 */
class EloquentBuilderMixinTest extends TestCase {
    use WithTestObject;

    public function testIterator(): void {
        $actual = null;
        $model  = new class() extends Model {
            // empty
        };

        self::assertTrue(Builder::hasGlobalMacro('getChunkedIterator'));

        try {
            $actual = $model->query()->getChunkedIterator();
        } catch (Exception) {
            // empty
        }

        self::assertInstanceOf(ChunkedIterator::class, $actual);
    }

    public function testChangeSafeIterator(): void {
        $actual = null;
        $model  = new class() extends Model {
            // empty
        };

        self::assertTrue(Builder::hasGlobalMacro('getChangeSafeIterator'));

        try {
            $actual = $model->query()->getChangeSafeIterator();
        } catch (Exception) {
            // empty
        }

        self::assertInstanceOf(ChunkedChangeSafeIterator::class, $actual);
    }

    public function testOrderByKey(): void {
        $a = TestObject::factory()->create();
        $b = TestObject::factory()->create();

        self::assertEquals([$a, $b], TestObject::query()->orderByKey()->get()->all());
        self::assertEquals([$b, $a], TestObject::query()->orderByKey('desc')->get()->all());
        self::assertEquals([$b, $a], TestObject::query()->orderByKeyDesc()->get()->all());
    }
}
