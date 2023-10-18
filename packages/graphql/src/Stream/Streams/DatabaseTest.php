<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Streams;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\Stream\Cursor;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\TestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\WithTestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_slice;
use function max;
use function usort;

/**
 * @internal
 */
#[CoversClass(Database::class)]
class DatabaseTest extends TestCase {
    use WithFaker;
    use WithQueryLog;
    use WithTestObject;

    public function testGetItems(): void {
        $limit   = max(1, $this->faker->numberBetween(1, 4));
        $offset  = max(0, $this->faker->numberBetween(0, 2));
        $cursor  = new Cursor('path', null, $offset);
        $builder = TestObject::query();
        $stream  = new Database($builder, $builder->getModel()->getKeyName(), $cursor, $limit);
        $objects = [
            TestObject::factory()->create(),
            TestObject::factory()->create(),
            TestObject::factory()->create(),
            TestObject::factory()->create(),
            TestObject::factory()->create(),
        ];

        usort($objects, static fn (TestObject $a, TestObject $b): int => $a->getKey() <=> $b->getKey());

        $expected = array_slice($objects, $offset, $limit);
        $query    = $this->getQueryLog();
        $items    = $stream->getItems();

        $stream->getItems(); // should be cached

        self::assertEquals($expected, [...$items]);
        self::assertQueryLogEquals(
            [
                [
                    'query'    => "select * from \"test_objects\" order by \"id\" asc limit {$limit} offset {$offset}",
                    'bindings' => [],
                ],
            ],
            $query,
        );
    }

    public function testGetLength(): void {
        $count   = $this->faker->numberBetween(1, 5);
        $builder = TestObject::query();
        $stream  = new Database($builder, 'key', new Cursor('path', null, 0), 1);

        for ($i = 0; $i < $count; $i++) {
            TestObject::factory()->create();
        }

        $query  = $this->getQueryLog();
        $length = $stream->getLength();

        $stream->getLength(); // should be cached

        self::assertEquals($count, $length);
        self::assertQueryLogEquals(
            [
                [
                    'query'    => 'select count(*) as aggregate from "test_objects"',
                    'bindings' => [],
                ],
            ],
            $query,
        );
    }

    public function testGetCurrentCursor(): void {
        $builder = TestObject::query();
        $cursor  = new Cursor('path', null, 0);
        $stream  = new Database($builder, 'key', $cursor, 2);

        self::assertSame($cursor, $stream->getCurrentCursor());
    }

    public function testGetPreviousCursor(): void {
        // Offset = 0
        $builder = TestObject::query();
        $cursor  = new Cursor('path', null, 0);
        $stream  = new Database($builder, 'key', $cursor, 2);

        self::assertNull($stream->getPreviousCursor());

        // Offset > 0
        $cursor = new Cursor('path', null, 1);
        $stream = new Database($builder, 'key', $cursor, 2);

        self::assertEquals(
            new Cursor('path', null, 0),
            $stream->getPreviousCursor(),
        );
    }

    public function testGetNextCursor(): void {
        // Prepare
        $builder = TestObject::query();
        $cursor  = new Cursor('path', null, 0);
        $stream  = Mockery::mock(Database::class, [$builder, 'key', $cursor, 2]);
        $stream->shouldAllowMockingProtectedMethods();
        $stream->makePartial();

        // Items.length < limit
        $stream
            ->shouldReceive('getCollection')
            ->once()
            ->andReturn(
                Collection::make([
                    TestObject::factory()->make(),
                ]),
            );

        self::assertNull($stream->getNextCursor());

        // Items.length = limit
        $stream
            ->shouldReceive('getCollection')
            ->once()
            ->andReturn(
                Collection::make([
                    TestObject::factory()->make(),
                    TestObject::factory()->make(),
                ]),
            );

        self::assertEquals(
            new Cursor('path', null, 2),
            $stream->getNextCursor(),
        );
    }
}
