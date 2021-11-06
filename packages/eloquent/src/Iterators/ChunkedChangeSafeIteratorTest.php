<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\WithTestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog;
use Mockery;

use function count;
use function iterator_to_array;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Iterators\ChunkedChangeSafeIterator
 */
class ChunkedChangeSafeIteratorTest extends TestCase {
    use WithTestObject;
    use WithQueryLog;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::getIterator
     * @covers ::each
     */
    public function testGetIterator(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $spyBefore = Mockery::spy(static fn() => null);
        $spyAfter  = Mockery::spy(static fn() => null);
        $db        = $this->app->make('db');
        $log       = $this->getQueryLog($db);
        $query     = TestObject::query()->orderByDesc('value');
        $count     = count($log);
        $iterator  = (new ChunkedChangeSafeIterator($query))
            ->setChunkSize(2)
            ->onBeforeChunk(
                Closure::fromCallable($spyBefore),
            )
            ->onAfterChunk(
                Closure::fromCallable($spyAfter),
            );

        $actual = [];

        foreach ($iterator as $model) {
            $actual[] = $model;

            if (count($actual) === 3) {
                TestObject::factory()->create(['value' => '4']);
            }
        }

        $count    = count($log) - $count;
        $key      = (new TestObject())->getKeyName();
        $expected = (clone $query)->reorder($key)->get()->all();

        self::assertEquals($expected, $actual);
        self::assertEquals(5, $count);
        // 1 - first chunk
        // 2 - second chunk
        // 3 - create #4
        // 4 - third chunk (because second chunk returned value)
        // 5 - last empty chunk (because third chunk returned value)

        $spyBefore
            ->shouldHaveBeenCalled()
            ->times(3);
        $spyAfter
            ->shouldHaveBeenCalled()
            ->times(3);
    }

    /**
     * @covers ::getIterator
     * @covers ::getDefaultLimit
     * @covers ::getDefaultOffset
     *
     * @dataProvider dataProviderGetIteratorColumn
     */
    public function testGetIteratorQueryDefaults(string $column): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $db       = $this->app->make('db');
        $table    = (new TestObject())->getTable();
        $query    = $db->table($table)->select()->limit(2)->offset(1)->orderByDesc('value');
        $iterator = (new ChunkedChangeSafeIterator($query, $column))->setChunkSize(1);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->offset(0)->count();
        $expected = (clone $query)->reorder()->offset(0)->orderBy($column)->limit(2)->get()->all();

        self::assertEquals(3, $count);
        self::assertCount(2, $actual);
        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::getIterator
     * @covers ::getDefaultLimit
     * @covers ::getDefaultOffset
     *
     * @dataProvider dataProviderGetIteratorColumn
     */
    public function testGetIteratorEloquentDefaults(string $column): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $query    = TestObject::query()->limit(2)->offset(1)->orderByDesc('value');
        $iterator = (new ChunkedChangeSafeIterator($query, $column))->setChunkSize(1);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->offset(0)->count();
        $expected = (clone $query)->reorder()->offset(0)->orderBy($column)->limit(2)->get()->all();

        self::assertEquals(3, $count);
        self::assertCount(2, $actual);
        self::assertEquals($expected, $actual);
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIteratorUnion(): void {
        self::expectExceptionObject(new InvalidArgumentException('Queries with UNION is not supported.'));

        new ChunkedChangeSafeIterator(TestObject::query()->union(TestObject::query()->toBase()));
    }

    /**
     * @covers ::getDefaultColumn
     *
     * @dataProvider dataProviderGetDefaultColumn
     *
     * @param Closure(): (EloquentBuilder|QueryBuilder) $factory
     */
    public function testGetDefaultColumn(string $expected, Closure $factory): void {
        $iterator = new class() extends ChunkedChangeSafeIterator {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }

            public function getDefaultColumn(EloquentBuilder|QueryBuilder $builder): string {
                return parent::getDefaultColumn($builder);
            }
        };

        self::assertEquals($expected, $iterator->getDefaultColumn($factory()));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string}>
     */
    public function dataProviderGetIteratorColumn(): array {
        return [
            'short'     => ['value'],
            'qualified' => ['test_objects.value'],
        ];
    }

    /**
     * @return array<string,array{string,Closure(): (EloquentBuilder|QueryBuilder)}>
     */
    public function dataProviderGetDefaultColumn(): array {
        return [
            QueryBuilder::class    => [
                'test_objects.id',
                static function (): QueryBuilder {
                    return TestObject::query()->toBase();
                },
            ],
            EloquentBuilder::class => [
                'test_objects.id',
                static function (): EloquentBuilder {
                    return TestObject::query();
                },
            ],
        ];
    }
    // </editor-fold>
}
