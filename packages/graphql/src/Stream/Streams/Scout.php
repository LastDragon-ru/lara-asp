<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Streams;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Stream\Offset;
use LastDragon_ru\LaraASP\GraphQL\Stream\Utils\Page;
use Override;

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

    public function __construct(object $builder, string $key, int $limit, Offset $offset) {
        parent::__construct($builder, $key, $limit, $offset);

        $this->page = new Page($limit, max(0, Cast::toInt($offset->offset)));
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getItems(): iterable {
        $items = $this->getPaginator()->items();
        $items = array_slice($items, $this->page->start, $this->page->length);

        return $items;
    }

    #[Override]
    public function getLength(): ?int {
        return max(0, $this->getPaginator()->total());
    }

    #[Override]
    public function getNextOffset(): ?Offset {
        return $this->page->end > 0 || $this->getPaginator()->hasMorePages()
            ? new Offset($this->offset->path, (int) $this->offset->offset + $this->limit, $this->offset->cursor)
            : null;
    }

    #[Override]
    public function getPreviousOffset(): ?Offset {
        return (int) $this->offset->offset > 0
            ? new Offset($this->offset->path, 0, $this->offset->cursor)
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
