<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use LastDragon_ru\LaraASP\Eloquent\Testing\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Models\TestObjectTrait;
use LastDragon_ru\LaraASP\Testing\Database\WithQueryLog;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use function count;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedChangeSafeIterator
 */
class ChunkedChangeSafeIteratorTest extends TestCase {
    use TestObjectTrait;
    use WithQueryLog;

    /**
     * @covers ::getIterator
     */
    public function testGetIterator() {
        $a = TestObject::factory()->create(['value' => '1']);
        $b = TestObject::factory()->create(['value' => '2']);
        $c = TestObject::factory()->create(['value' => '3']);

        $db       = $this->app->make('db');
        $query    = TestObject::query()->orderByDesc('value');
        $count    = count($db->getQueryLog());
        $iterator = new ChunkedChangeSafeIterator(2, $query);
        $actual   = [];

        foreach ($iterator as $model) {
            $actual[] = $model;

            if (count($actual) == 3) {
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
}
