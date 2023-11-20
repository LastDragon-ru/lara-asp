<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Iterators;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Override;

use function end;
use function explode;
use function trim;

/**
 * The iterator that grabs rows by chunk and safe for changing/deleting rows
 * while iteration.
 *
 * Similar to {@link \Illuminate\Database\Query\Builder::chunkById()} but uses
 * generators instead of {@link \Closure}. Although you can modify/delete the
 * items while iteration there are few important limitations:
 *
 * - it is not possible to sort rows, they always will be sorted by `column asc`;
 * - the `column` should not be changed while iteration or this may lead to
 *   repeating row in results;
 * - the row inserted while iteration may be skipped if it has `column` with
 *   the value that is lower than the internal pointer, or it was inserted after
 *   the last chunk loaded;
 * - queries with UNION are not supported.
 *
 * @see      https://github.com/laravel/framework/issues/35400
 *
 * @template TItem of Model
 *
 * @extends IteratorImpl<TItem>
 */
class ChunkedChangeSafeIterator extends IteratorImpl {
    private string $column;

    public function __construct(Builder $builder, string $column = null) {
        parent::__construct($builder);

        $this->column = $column ?? $this->getDefaultColumn($builder);

        // Unfortunately the Laravel doesn't correctly work with UNION,
        // it just adds conditional to the main query, and this leads to an
        // infinite loop.
        if ($this->hasUnions()) {
            throw new InvalidArgumentException('Query with UNION is not supported.');
        }
    }

    public function getColumn(): string {
        return $this->column;
    }

    #[Override]
    protected function getChunk(Builder $builder, int $chunk): Collection {
        $column = $this->getColumn();

        $builder
            ->reorder()
            ->orderBy($column)
            ->limit($chunk)
            ->when(
                $this->getOffset(),
                static function (Builder $builder, string|int|null $offset) use ($column): void {
                    $builder->where($column, '>', $offset);
                },
            );

        return $builder->get();
    }

    #[Override]
    protected function chunkProcessed(Collection $items): bool {
        $last = $this->column($items->last());

        if ($last) {
            $this->setOffset($last);
        }

        return parent::chunkProcessed($items)
            && $last;
    }

    /**
     * @param TItem|null $item
     */
    protected function column(Model|null $item): mixed {
        $value  = null;
        $column = explode('.', $this->getColumn());
        $column = trim(end($column), '`"[]');

        if ($item) {
            $value = $item->getAttribute($column);
        }

        return $value;
    }

    protected function hasUnions(): bool {
        return (bool) $this->getBuilder()->getQuery()->unions;
    }

    #[Override]
    protected function getDefaultOffset(): ?int {
        // Because Builder contains SQL offset, not column value.
        return null;
    }

    /**
     * @param Builder<TItem> $builder
     */
    protected function getDefaultColumn(Builder $builder): string {
        $column = $builder->getModel()->getKeyName();
        $column = $builder->qualifyColumn($column);

        return $column;
    }
}
