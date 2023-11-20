<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Streams;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Stream\Offset;
use Override;
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
    #[Override]
    public function getItems(): iterable {
        return $this->getCollection();
    }

    #[Override]
    public function getLength(): ?int {
        if ($this->length === null) {
            $this->length = max(0, $this->builder->count());
        }

        return $this->length;
    }

    #[Override]
    public function getNextOffset(): ?Offset {
        return $this->getCollection()->count() >= $this->limit
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
     * @return Collection<array-key, EloquentModel|stdClass>
     */
    protected function getCollection(): Collection {
        if ($this->collection === null) {
            $this->collection = (clone $this->builder)
                ->orderBy($this->key)
                ->offset(Cast::toInt($this->offset->offset))
                ->limit($this->limit)
                ->get();
        }

        return $this->collection;
    }
}
