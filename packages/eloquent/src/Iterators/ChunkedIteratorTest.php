<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\WithTestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Database\WithQueryLog;
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
     * @covers ::each
     */
    public function testGetIterator(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $spy      = Mockery::spy(static fn() => null);
        $db       = $this->app->make('db');
        $table    = (new TestObject())->getTable();
        $query    = $db->table($table)->select()->orderByDesc('value');
        $expected = (clone $query)->get()->all();
        $count    = count($db->getQueryLog());
        $iterator = (new ChunkedIterator(2, $query))->each(Closure::fromCallable($spy));
        $actual   = iterator_to_array($iterator);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(2, count($db->getQueryLog()) - $count);

        $spy
            ->shouldHaveBeenCalled()
            ->withArgs(static function (Collection $items): bool {
                return $items->count() >= 1;
            })
            ->twice();
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
        $iterator = new ChunkedIterator(1, $query);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->count();
        $expected = (clone $query)->limit(2)->get()->all();

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
        $iterator = new ChunkedIterator(1, $query);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->count();
        $expected = (clone $query)->limit(2)->get()->all();

        $this->assertEquals(3, $count);
        $this->assertCount(2, $actual);
        $this->assertEquals($expected, $actual);
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
        $iterator = new ChunkedIterator(1, $query->union($second->toBase()));
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->count();
        $expected = (clone $query)->get()->all();

        $this->assertCount(4, $actual);
        $this->assertEquals(4, $count);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIteratorUnionLimit(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);
        TestObject::factory()->create(['value' => '4']);
        TestObject::factory()->create(['value' => '5']);

        $query    = TestObject::query()->where('value', '<', 4)->limit(2)->orderByDesc('value');
        $second   = TestObject::query()->where('value', '>=', 4)->limit(2)->orderByDesc('value');
        $iterator = new ChunkedIterator(1, $query->union($second->toBase())->limit(3));
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->count();
        $expected = (clone $query)->get()->all();

        $this->assertCount(3, $actual);
        $this->assertEquals(3, $count);
        $this->assertEquals($expected, $actual);
    }
}
