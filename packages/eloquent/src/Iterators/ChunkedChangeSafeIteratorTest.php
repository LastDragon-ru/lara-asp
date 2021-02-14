<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Eloquent\Testing\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Models\WithTestObject;
use LastDragon_ru\LaraASP\Testing\Database\WithQueryLog;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;

use function count;
use function iterator_to_array;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedChangeSafeIterator
 */
class ChunkedChangeSafeIteratorTest extends TestCase {
    use WithTestObject;
    use WithQueryLog;

    /**
     * @covers ::getIterator
     */
    public function testGetIterator(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $db       = $this->app->make('db');
        $query    = TestObject::query()->orderByDesc('value');
        $count    = count($db->getQueryLog());
        $iterator = new ChunkedChangeSafeIterator(2, $query);
        $actual   = [];

        foreach ($iterator as $model) {
            $actual[] = $model;

            if (count($actual) === 3) {
                TestObject::factory()->create(['value' => '4']);
            }
        }

        $count    = count($db->getQueryLog()) - $count;
        $key      = (new TestObject())->getKeyName();
        $expected = (clone $query)->reorder($key)->get()->all();

        $this->assertEquals($expected, $actual);
        $this->assertEquals(5, $count);
        // 1 - first chunk
        // 2 - second chunk
        // 3 - create #4
        // 4 - third chunk (because second chunk returned value)
        // 5 - last empty chunk (because third chunk returned value)
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIteratorLimit(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $db       = $this->app->make('db');
        $table    = (new TestObject())->getTable();
        $query    = $db->table($table)->select()->limit(2)->orderByDesc('value');
        $iterator = new ChunkedChangeSafeIterator(1, $query);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->count();
        $expected = (clone $query)->reorder()->orderBy('id')->limit(2)->get()->all();

        $this->assertEquals(3, $count);
        $this->assertCount(2, $actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIteratorLimitEloquent(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $query    = TestObject::query()->limit(2)->orderByDesc('value');
        $iterator = new ChunkedChangeSafeIterator(1, $query);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->count();
        $expected = (clone $query)->reorder()->orderByKey()->limit(2)->get()->all();

        $this->assertEquals(3, $count);
        $this->assertCount(2, $actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIteratorUnion(): void {
        $this->expectExceptionObject(new InvalidArgumentException('Queries with UNION is not supported.'));

        new ChunkedChangeSafeIterator(1, TestObject::query()->union(TestObject::query()->toBase()));
    }
}
