<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use LastDragon_ru\LaraASP\Eloquent\Testing\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Models\TestObjectTrait;
use LastDragon_ru\LaraASP\Testing\Database\WithQueryLog;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use function count;
use function iterator_to_array;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedIterator
 */
class ChunkedIteratorTest extends TestCase {
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
        $table    = (new TestObject())->getTable();
        $query    = $db->table($table)->select()->orderByDesc('value');
        $expected = (clone $query)->get()->all();
        $count    = count($db->getQueryLog());
        $iterator = new ChunkedIterator(2, $query);
        $actual   = iterator_to_array($iterator);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(2, count($db->getQueryLog()) - $count);
    }
}
