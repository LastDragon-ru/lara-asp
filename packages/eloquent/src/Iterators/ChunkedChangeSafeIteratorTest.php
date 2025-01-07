<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Closure;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\TestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models\WithTestObject;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function count;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(ChunkedChangeSafeIterator::class)]
final class ChunkedChangeSafeIteratorTest extends TestCase {
    use WithTestObject;
    use WithQueryLog;

    // <editor-fold desc="Tests">
    // =========================================================================
    public function testGetIterator(): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $spyBefore = Mockery::spy(static fn () => null);
        $spyAfter  = Mockery::spy(static fn () => null);
        $db        = $this->app()->make(ConnectionResolverInterface::class);
        $log       = $this->getQueryLog($db);
        $query     = TestObject::query()->orderByDesc('value');
        $count     = count($log);
        $iterator  = (new ChunkedChangeSafeIterator($query))
            ->setChunkSize(2)
            ->onBeforeChunk(
                $spyBefore(...),
            )
            ->onAfterChunk(
                $spyAfter(...),
            );

        $actual = [];

        foreach ($iterator as $model) {
            $actual[] = $model;

            if (count($actual) === 2) {
                TestObject::factory()->create(['value' => '4']);
            }
        }

        $count    = count($log) - $count;
        $key      = (new TestObject())->getKeyName();
        $expected = (clone $query)->reorder($key)->get()->all();

        self::assertEquals($expected, $actual);
        self::assertSame(4, $count);
        // 1 - first chunk
        // 2 - create #4
        // 3 - second chunk
        // 4 - third chunk (because second chunk returned value)

        self::assertSame(count($expected), $iterator->getIndex());
        self::assertSame(count($expected), $iterator->getOffset());
        self::assertSame(4, count($iterator));

        $spyBefore
            ->shouldHaveBeenCalled()
            ->times(2);
        $spyAfter
            ->shouldHaveBeenCalled()
            ->times(2);
    }

    #[DataProvider('dataProviderGetIteratorColumn')]
    public function testGetIteratorDefaults(string $column): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $query    = TestObject::query()->limit(2)->offset(1)->orderByDesc('value');
        $iterator = (new ChunkedChangeSafeIterator($query, $column))->setChunkSize(1);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->offset(0)->count();
        $expected = (clone $query)->reorder()->offset(0)->orderBy($column)->limit(2)->get()->all();

        self::assertSame(3, $count);
        self::assertCount(2, $actual);
        self::assertEquals($expected, $actual);
    }

    #[DataProvider('dataProviderGetIteratorColumn')]
    public function testGetIteratorEloquentDefaults(string $column): void {
        TestObject::factory()->create(['value' => '1']);
        TestObject::factory()->create(['value' => '2']);
        TestObject::factory()->create(['value' => '3']);

        $query    = TestObject::query()->limit(2)->offset(1)->orderByDesc('value');
        $iterator = (new ChunkedChangeSafeIterator($query, $column))->setChunkSize(1);
        $actual   = iterator_to_array($iterator);
        $count    = (clone $query)->offset(0)->count();
        $expected = (clone $query)->reorder()->offset(0)->orderBy($column)->limit(2)->get()->all();

        self::assertSame(3, $count);
        self::assertCount(2, $actual);
        self::assertEquals($expected, $actual);
    }

    public function testGetIteratorUnion(): void {
        self::expectExceptionObject(new InvalidArgumentException('Query with UNION is not supported.'));

        new ChunkedChangeSafeIterator(TestObject::query()->union(TestObject::query()->getQuery()));
    }

    /**
     * @param Closure(): Builder<Model> $factory
     */
    #[DataProvider('dataProviderGetDefaultColumn')]
    public function testGetDefaultColumn(string $expected, Closure $factory): void {
        $iterator = new class() extends ChunkedChangeSafeIterator {
            /** @noinspection PhpMissingParentConstructorInspection */
            public function __construct() {
                // empty
            }

            #[Override]
            public function getDefaultColumn(Builder $builder): string {
                return parent::getDefaultColumn($builder);
            }
        };

        self::assertSame($expected, $iterator->getDefaultColumn($factory()));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string}>
     */
    public static function dataProviderGetIteratorColumn(): array {
        return [
            'short'     => ['value'],
            'qualified' => ['test_objects.value'],
        ];
    }

    /**
     * @return array<string,array{string, Closure(): Builder<TestObject>}>
     */
    public static function dataProviderGetDefaultColumn(): array {
        return [
            Builder::class => [
                'test_objects.id',
                static function (): Builder {
                    return TestObject::query();
                },
            ],
        ];
    }
    // </editor-fold>
}
