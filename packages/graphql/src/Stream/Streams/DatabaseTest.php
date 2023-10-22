<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Streams;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\Stream\Offset;
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
        $builder = TestObject::query();
        $stream  = new Database(
            $builder,
            $builder->getModel()->getKeyName(),
            $limit,
            new Offset('path', $offset, null),
        );
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
        $stream  = new Database($builder, 'key', 1, new Offset('path', 0, null));

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

    public function testGetCurrentOffset(): void {
        $builder = TestObject::query();
        $offset  = new Offset('path', 0, null);
        $stream  = new Database($builder, 'key', 2, $offset);

        self::assertSame($offset, $stream->getCurrentOffset());
    }

    public function testGetPreviousOffset(): void {
        // Offset = 0
        $builder = TestObject::query();
        $offset  = new Offset('path', 0, null);
        $stream  = new Database($builder, 'key', 2, $offset);

        self::assertNull($stream->getPreviousOffset());

        // Offset > 0
        $offset = new Offset('path', 1, null);
        $stream = new Database($builder, 'key', 2, $offset);

        self::assertEquals(
            new Offset('path', 0, null),
            $stream->getPreviousOffset(),
        );
    }

    public function testGetNextOffset(): void {
        // Prepare
        $builder = TestObject::query();
        $offset  = new Offset('path', 0, null);
        $stream  = Mockery::mock(Database::class, [$builder, 'key', 2, $offset]);
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

        self::assertNull($stream->getNextOffset());

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
            new Offset('path', 2, null),
            $stream->getNextOffset(),
        );
    }
}
