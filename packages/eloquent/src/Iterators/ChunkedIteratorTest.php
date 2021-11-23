<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\WithTestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog;
use Mockery;

use function count;
use function iterator_to_array;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedIterator
 */
class ChunkedIteratorTest extends TestCase {
    use WithTestObject;
    use WithQueryLog;

    /**
     * @covers ::getIterator
     * @covers ::getOffset
     * @covers ::getIndex
     */
    public function testGetIterator(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $spyBefore = Mockery::spy(static fn() => null);
        $spyAfter  = Mockery::spy(static fn() => null);
        $db        = $this->app->make('db');
        $log       = $this->getQueryLog($db);
        $table     = (new TestObject())->getTable();
        $query     = $db->table($table)->select()->orderByDesc('value');
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
        self::assertEquals(2, count($log) - $count);
        self::assertEquals(count($expected), $iterator->getIndex());
        self::assertEquals(count($expected), $iterator->getOffset());

        $spyBefore
            ->shouldHaveBeenCalled()
            ->once();
        $spyAfter
            ->shouldHaveBeenCalled()
            ->once();
    }

    /**
     * @covers ::getIterator
     * @covers ::getDefaultLimit
     * @covers ::getDefaultOffset
     */
    public function testGetIteratorQueryDefaults(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $db       = $this->app->make('db');
        $table    = (new TestObject())->getTable();
        $query    = $db->table($table)->limit(2)->offset(1)->orderByDesc('value');
        $iterator = (new ChunkedIterator($query))->setChunkSize(1);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->offset(0)->count();
        $expected = (clone $query)->get()->all();

        self::assertEquals(3, $count);
        self::assertCount(2, $actual);
        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::getIterator
     * @covers ::getDefaultLimit
     * @covers ::getDefaultOffset
     */
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

    /**
     * @covers ::getIterator
     */
    public function testGetIteratorUnion(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);
        TestObject::factory()->create(['value' => '4']);
        TestObject::factory()->create(['value' => '5']);

        $query    = TestObject::query()->where('value', '<', 4)->limit(2)->orderByDesc('value');
        $second   = TestObject::query()->where('value', '>=', 4)->limit(2)->orderByDesc('value');
        $iterator = new ChunkedIterator($query->union($second->toBase()));
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->count();
        $expected = (clone $query)->get()->all();

        self::assertCount(4, $actual);
        self::assertEquals(4, $count);
        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::getIterator
     * @covers ::getDefaultLimit
     * @covers ::getDefaultOffset
     */
    public function testGetIteratorUnionLimit(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);
        TestObject::factory()->create(['value' => '4']);
        TestObject::factory()->create(['value' => '5']);

        $query    = TestObject::query()->where('value', '<', 4)->limit(2)->orderByDesc('value');
        $second   = TestObject::query()->where('value', '>=', 4)->limit(2)->orderByDesc('value');
        $iterator = new ChunkedIterator($query->union($second->toBase())->limit(3)->offset(1));
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->offset(0)->count();
        $expected = (clone $query)->get()->all();

        self::assertCount(3, $actual);
        self::assertEquals(3, $count);
        self::assertEquals($expected, $actual);
    }
}
