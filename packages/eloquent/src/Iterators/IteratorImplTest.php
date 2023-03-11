<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\Eloquent\Testing\Package\TestCase;
use Mockery;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\Eloquent\Iterators\IteratorImpl
 */
class IteratorImplTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderCount
     */
    public function testCount(int $expected, ?int $limit, int $count): void {
        $query = Mockery::mock(QueryBuilder::class);
        $query
            ->shouldReceive('count')
            ->once()
            ->andReturn($count);

        $builder = Mockery::mock(EloquentBuilder::class);
        $builder
            ->shouldReceive('toBase')
            ->atLeast()
            ->once()
            ->andReturn($query);

        $iterator = new class($builder) extends IteratorImpl {
            protected function getChunk(EloquentBuilder $builder, int $chunk): Collection {
                return new Collection();
            }
        };

        self::assertCount($expected, $iterator->setLimit($limit));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{int, ?int, int}>
     */
    public function dataProviderCount(): array {
        return [
            'count only'    => [123, null, 123],
            'count invalid' => [0, null, -123],
            'limit < count' => [1, 1, 2],
            'limit > count' => [1, 2, 1],
        ];
    }
    // </editor-fold>
}
