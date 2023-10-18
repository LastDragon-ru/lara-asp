<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Streams;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Stream\Cursor;
use LastDragon_ru\LaraASP\GraphQL\Stream\Utils\Page;

use function array_slice;
use function max;

/**
 * @extends Stream<ScoutBuilder>
 */
class Scout extends Stream {
    /**
     * @var LengthAwarePaginator<object>|null
     */
    private ?LengthAwarePaginator $paginator = null;

    protected readonly Page $page;

    public function __construct(object $builder, string $key, Cursor $cursor, int $limit) {
        parent::__construct($builder, $key, $cursor, $limit);

        $this->page = new Page($limit, max(0, Cast::toInt($cursor->offset)));
    }

    /**
     * @inheritDoc
     */
    public function getItems(): iterable {
        $items = $this->getPaginator()->items();
        $items = array_slice($items, $this->page->start, $this->page->length);

        return $items;
    }

    public function getLength(): ?int {
        return max(0, $this->getPaginator()->total());
    }

    public function getNextCursor(): ?Cursor {
        return $this->page->end > 0 || $this->getPaginator()->hasMorePages()
            ? new Cursor($this->cursor->path, $this->cursor->cursor, (int) $this->cursor->offset + $this->limit)
            : null;
    }

    public function getPreviousCursor(): ?Cursor {
        return (int) $this->cursor->offset > 0
            ? new Cursor($this->cursor->path, $this->cursor->cursor, 0)
            : null;
    }

    /**
     * @return LengthAwarePaginator<object>
     */
    protected function getPaginator(): LengthAwarePaginator {
        if ($this->paginator === null) {
            $this->paginator = (clone $this->builder)
                ->orderBy($this->key)
                ->paginate($this->page->pageSize, 'page', $this->page->pageNumber);
        }

        return $this->paginator;
    }
}
