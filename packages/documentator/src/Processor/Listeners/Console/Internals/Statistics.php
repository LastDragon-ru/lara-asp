<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Internals;

use ArrayAccess;
use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums\Flag;
use Override;
use SplObjectStorage;
use Traversable;

use function iterator_to_array;

/**
 * @internal
 * @implements IteratorAggregate<Flag, Usage>
 * @implements ArrayAccess<Flag, Usage>
 */
class Statistics implements IteratorAggregate, ArrayAccess {
    /**
     * @var SplObjectStorage<Flag, Usage>
     */
    protected SplObjectStorage $storage;

    public function __construct() {
        $this->storage = new SplObjectStorage();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getIterator(): Traversable {
        foreach ($this->storage as $flag) {
            yield $flag => $this->storage[$flag];
        }
    }

    /**
     * @return list<Flag>
     */
    public function flags(): array {
        return iterator_to_array($this->storage, false);
    }

    public function merge(self $statistics): static {
        foreach ($statistics as $flag => $statistic) {
            if (isset($this->storage[$flag])) {
                $this->storage[$flag]->time  += $statistic->time;
                $this->storage[$flag]->count += $statistic->count;
                $this->storage[$flag]->bytes += $statistic->bytes;
            } else {
                $this->storage[$flag] = new Usage(
                    $statistic->time,
                    $statistic->count,
                    $statistic->bytes,
                );
            }
        }

        return $this;
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        return isset($this->storage[$offset]);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        return $this->storage[$offset];
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        $this->storage[$offset] = $value;
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        unset($this->storage[$offset]);
    }
}
