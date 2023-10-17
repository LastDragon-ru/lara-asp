<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Streams;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Stream\Cursor;
use stdClass;

use function max;

/**
 * @extends Stream<EloquentBuilder<covariant EloquentModel>|QueryBuilder>
 */
class Database extends Stream {
    /**
     * @var Collection<array-key, EloquentModel|stdClass>|null
     */
    private ?Collection $collection = null;

    /**
     * @var int<0, max>|null
     */
    private ?int $length = null;

    /**
     * @inheritDoc
     */
    public function getItems(): iterable {
        return $this->getCollection();
    }

    public function getLength(): ?int {
        if ($this->length === null) {
            $this->length = max(0, $this->builder->count());
        }

        return $this->length;
    }

    public function getNextCursor(): ?Cursor {
        return $this->getCollection()->count() >= $this->limit
            ? new Cursor($this->cursor->path, $this->cursor->cursor, (int) $this->cursor->offset + $this->limit)
            : null;
    }

    public function getPreviousCursor(): ?Cursor {
        return (int) $this->cursor->offset > 0
            ? new Cursor($this->cursor->path, $this->cursor->cursor, 0)
            : null;
    }

    /**
     * @return Collection<array-key, EloquentModel|stdClass>
     */
    protected function getCollection(): Collection {
        if ($this->collection === null) {
            $this->collection = (clone $this->builder)
                ->orderBy($this->key)
                ->offset(Cast::toInt($this->cursor->offset))
                ->limit($this->limit)
                ->get();
        }

        return $this->collection;
    }
}
