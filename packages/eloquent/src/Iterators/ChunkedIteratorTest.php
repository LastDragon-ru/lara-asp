<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\WithTestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function count;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(ChunkedIterator::class)]
class ChunkedIteratorTest extends TestCase {
    use WithTestObject;
    use WithQueryLog;

    public function testGetIterator(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $spyBefore = Mockery::spy(static fn() => null);
        $spyAfter  = Mockery::spy(static fn() => null);
        $db        = $this->app->make('db');
        $log       = $this->getQueryLog($db);
        $query     = TestObject::query()->orderByDesc('value');
        $expected  = (clone $query)->get()->all();
        $count     = count($log);
        $iterator  = (new ChunkedIterator($query))
            ->onBeforeChunk(
                Closure::fromCallable($spyBefore),
            )
            ->onAfterChunk(
                Closure::fromCallable($spyAfter),
            );

        $actual = iterator_to_array($iterator);

        self::assertEquals($expected, $actual);
        self::assertEquals(1, count($log) - $count);
        self::assertEquals(count($expected), $iterator->getIndex());
        self::assertEquals(count($expected), $iterator->getOffset());
        self::assertEquals(3, count($iterator));

        $spyBefore
            ->shouldHaveBeenCalled()
            ->once();
        $spyAfter
            ->shouldHaveBeenCalled()
            ->once();
    }

    public function testGetIteratorDefaults(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $query    = TestObject::query()->limit(2)->offset(1)->orderByDesc('value');
        $iterator = (new ChunkedIterator($query))->setChunkSize(1);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->offset(0)->count();
        $expected = (clone $query)->get()->all();

        self::assertEquals(3, $count);
        self::assertCount(2, $actual);
        self::assertEquals($expected, $actual);
    }

    public function testGetIteratorEloquentDefaults(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $query    = TestObject::query()->limit(2)->offset(1)->orderByDesc('value');
        $iterator = (new ChunkedIterator($query))->setChunkSize(1);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->offset(0)->count();
        $expected = (clone $query)->get()->all();

        self::assertEquals(3, $count);
        self::assertCount(2, $actual);
        self::assertEquals($expected, $actual);
    }

    public function testGetIteratorUnion(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);
        TestObject::factory()->create(['value' => '4']);
        TestObject::factory()->create(['value' => '5']);

        $query    = TestObject::query()->where('value', '<', 4)->limit(2)->orderByDesc('value');
        $second   = TestObject::query()->where('value', '>=', 4)->limit(2)->orderByDesc('value');
        $iterator = new ChunkedIterator($query->union($second->getQuery()));
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->count();
        $expected = (clone $query)->get()->all();

        self::assertCount(4, $actual);
        self::assertEquals(4, $count);
        self::assertEquals($expected, $actual);
    }

    public function testGetIteratorUnionLimit(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);
        TestObject::factory()->create(['value' => '4']);
        TestObject::factory()->create(['value' => '5']);

        $query    = TestObject::query()->where('value', '<', 4)->limit(2)->orderByDesc('value');
        $second   = TestObject::query()->where('value', '>=', 4)->limit(2)->orderByDesc('value');
        $iterator = new ChunkedIterator($query->union($second->getQuery())->limit(3)->offset(1));
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->offset(0)->count();
        $expected = (clone $query)->get()->all();

        self::assertCount(3, $actual);
        self::assertEquals(3, $count);
        self::assertEquals($expected, $actual);
    }
}
