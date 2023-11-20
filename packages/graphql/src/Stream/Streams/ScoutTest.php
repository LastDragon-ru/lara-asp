<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Streams;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\WithFaker;
use LastDragon_ru\LaraASP\GraphQL\Stream\Offset;
use LastDragon_ru\LaraASP\GraphQL\Stream\Utils\Page;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\TestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models\WithTestObject;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog;
use Mockery;
use Override;
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
    #[Override]
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
        $builder = TestObject::search();
        $stream  = new Scout($builder, $builder->model->getKeyName(), $limit, new Offset('path', $offset, null));
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
        $stream  = new Scout($builder, $builder->model->getKeyName(), 1, new Offset('path', 0, null));

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

    public function testGetCurrentOffset(): void {
        $builder = TestObject::search();
        $offset  = new Offset('path', 0, null);
        $stream  = new Scout($builder, 'key', 2, $offset);

        self::assertSame($offset, $stream->getCurrentOffset());
    }

    public function testGetPreviousOffset(): void {
        // Offset = 0
        $builder = TestObject::search();
        $offset  = new Offset('path', 0, null);
        $stream  = new Scout($builder, 'key', 2, $offset);

        self::assertNull($stream->getPreviousOffset());

        // Offset > 0
        $offset = new Offset('path', 1, null);
        $stream = new Scout($builder, 'key', 2, $offset);

        self::assertEquals(
            new Offset('path', 0, null),
            $stream->getPreviousOffset(),
        );
    }

    public function testGetNextOffset(): void {
        // Page.end = 0 & no more pages
        $builder   = TestObject::search();
        $offset    = new Offset('path', 0, null);
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $paginator
            ->shouldReceive('hasMorePages')
            ->once()
            ->andReturn(false);
        $stream = Mockery::mock(Scout::class, [$builder, 'key', 2, $offset]);
        $stream->shouldAllowMockingProtectedMethods();
        $stream->makePartial();
        $stream
            ->shouldReceive('getPaginator')
            ->once()
            ->andReturn($paginator);

        self::assertNull($stream->getNextOffset());

        // Page.end > 0 & no more pages
        $builder = TestObject::search();
        $offset  = new Offset('path', 1234, null);
        $stream  = Mockery::mock(Scout::class, [$builder, 'key', 123, $offset]);
        $stream->shouldAllowMockingProtectedMethods();
        $stream->makePartial();
        $stream
            ->shouldReceive('getPaginator')
            ->never();

        self::assertEquals(
            new Offset('path', 1234 + 123, null),
            $stream->getNextOffset(),
        );

        // Page.end = 0 & has more pages
        $builder   = TestObject::search();
        $offset    = new Offset('path', 0, null);
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $paginator
            ->shouldReceive('hasMorePages')
            ->once()
            ->andReturn(true);
        $stream = Mockery::mock(Scout::class, [$builder, 'key', 2, $offset]);
        $stream->shouldAllowMockingProtectedMethods();
        $stream->makePartial();
        $stream
            ->shouldReceive('getPaginator')
            ->once()
            ->andReturn($paginator);

        self::assertEquals(
            new Offset('path', 2, null),
            $stream->getNextOffset(),
        );
    }
    // </editor-fold>
}
