<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\Eloquent\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(IteratorImpl::class)]
final class IteratorImplTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderCount')]
    public function testCount(int $expected, ?int $limit, int $count): void {
        $query = Mockery::mock(QueryBuilder::class);
        $query
            ->shouldReceive('count')
            ->once()
            ->andReturn($count);

        $builder = Mockery::mock(EloquentBuilder::class);
        $builder
            ->shouldReceive('getQuery')
            ->atLeast()
            ->once()
            ->andReturn($query);

        $iterator = new class($builder) extends IteratorImpl {
            #[Override]
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
    public static function dataProviderCount(): array {
        return [
            'count only'    => [123, null, 123],
            'count invalid' => [0, null, -123],
            'limit < count' => [1, 1, 2],
            'limit > count' => [1, 2, 1],
        ];
    }
    // </editor-fold>
}
