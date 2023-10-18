<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Streams;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\WithFaker;
use LastDragon_ru\LaraASP\GraphQL\Stream\Cursor;
use LastDragon_ru\LaraASP\GraphQL\Stream\Utils\Page;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\TestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\WithTestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_slice;
use function config;
use function max;
use function usort;

/**
 * @internal
 */
#[CoversClass(Scout::class)]
class ScoutTest extends TestCase {
    use WithFaker;
    use WithQueryLog;
    use WithTestObject;

    // <editor-fold desc="Prepare">
    // =========================================================================
    /**
     * @inheritDoc
     */
    protected function getEnvironmentSetUp($app): void {
        parent::getEnvironmentSetUp($app);

        config([
            'scout.driver' => 'database',
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    public function testGetItems(): void {
        $limit   = max(1, $this->faker->numberBetween(1, 4));
        $offset  = max(0, $this->faker->numberBetween(0, 2));
        $cursor  = new Cursor('path', null, $offset);
        $builder = TestObject::search();
        $stream  = new Scout($builder, $builder->model->getKeyName(), $cursor, $limit);
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
        $page     = new Page($limit, $offset);
        $limit    = $page->pageSize;
        $offset   = $limit * ($page->pageNumber - 1);

        $stream->getItems(); // should be cached

        self::assertEquals($expected, [...$items]);
        self::assertQueryLogEquals(
            [
                [
                    'query'    => 'select count(*) as aggregate from "test_objects"',
                    'bindings' => [],
                ],
                [
                    'query'    => <<<SQL
                        select *
                        from "test_objects"
                        order by "id" asc, "id" desc
                        limit {$limit} offset {$offset}
                    SQL
                    ,
                    'bindings' => [],
                ],
            ],
            $query,
        );
    }

    public function testGetLength(): void {
        $count   = $this->faker->numberBetween(1, 5);
        $builder = TestObject::search();
        $stream  = new Scout($builder, $builder->model->getKeyName(), new Cursor('path', null, 0), 1);

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
                [
                    'query'    => <<<'SQL'
                        select *
                        from "test_objects"
                        order by "id" asc, "id" desc
                        limit 1 offset 0
                    SQL
                    ,
                    'bindings' => [],
                ],
            ],
            $query,
        );
    }

    public function testGetCurrentCursor(): void {
        $builder = TestObject::search();
        $cursor  = new Cursor('path', null, 0);
        $stream  = new Scout($builder, 'key', $cursor, 2);

        self::assertSame($cursor, $stream->getCurrentCursor());
    }

    public function testGetPreviousCursor(): void {
        // Offset = 0
        $builder = TestObject::search();
        $cursor  = new Cursor('path', null, 0);
        $stream  = new Scout($builder, 'key', $cursor, 2);

        self::assertNull($stream->getPreviousCursor());

        // Offset > 0
        $cursor = new Cursor('path', null, 1);
        $stream = new Scout($builder, 'key', $cursor, 2);

        self::assertEquals(
            new Cursor('path', null, 0),
            $stream->getPreviousCursor(),
        );
    }

    public function testGetNextCursor(): void {
        // Page.end = 0 & no more pages
        $builder   = TestObject::search();
        $cursor    = new Cursor('path', null, 0);
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $paginator
            ->shouldReceive('hasMorePages')
            ->once()
            ->andReturn(false);
        $stream = Mockery::mock(Scout::class, [$builder, 'key', $cursor, 2]);
        $stream->shouldAllowMockingProtectedMethods();
        $stream->makePartial();
        $stream
            ->shouldReceive('getPaginator')
            ->once()
            ->andReturn($paginator);

        self::assertNull($stream->getNextCursor());

        // Page.end > 0 & no more pages
        $builder = TestObject::search();
        $cursor  = new Cursor('path', null, 1234);
        $stream  = Mockery::mock(Scout::class, [$builder, 'key', $cursor, 123]);
        $stream->shouldAllowMockingProtectedMethods();
        $stream->makePartial();
        $stream
            ->shouldReceive('getPaginator')
            ->never();

        self::assertEquals(
            new Cursor('path', null, 1234 + 123),
            $stream->getNextCursor(),
        );

        // Page.end = 0 & has more pages
        $builder   = TestObject::search();
        $cursor    = new Cursor('path', null, 0);
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $paginator
            ->shouldReceive('hasMorePages')
            ->once()
            ->andReturn(true);
        $stream = Mockery::mock(Scout::class, [$builder, 'key', $cursor, 2]);
        $stream->shouldAllowMockingProtectedMethods();
        $stream->makePartial();
        $stream
            ->shouldReceive('getPaginator')
            ->once()
            ->andReturn($paginator);

        self::assertEquals(
            new Cursor('path', null, 2),
            $stream->getNextCursor(),
        );
    }
    // </editor-fold>
}
