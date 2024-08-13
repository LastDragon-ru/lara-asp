<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Generator;
use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;
use Traversable;

/**
 * @internal
 *
 * @template TKey
 * @template TValue of Directory|File
 *
 * @implements IteratorAggregate<TKey, TValue>
 */
class ExecutorTraversable implements IteratorAggregate {
    private float $duration = 0;

    public function __construct(
        /**
         * @var Dependency<Traversable<TKey, TValue>>
         */
        private readonly Dependency $dependency,
        /**
         * @var Traversable<TKey, TValue>
         */
        private readonly Traversable $resolved,
        /**
         * @var Closure(Dependency<Traversable<TKey, TValue>>, TValue): float
         */
        private readonly Closure $handler,
    ) {
        // empty
    }

    public function getDuration(): float {
        return $this->duration;
    }

    /**
     * @return Generator<TKey, TValue>
     */
    #[Override]
    public function getIterator(): Generator {
        $this->duration = 0;

        foreach ($this->resolved as $key => $value) {
            $this->duration += ($this->handler)($this->dependency, $value);

            yield $key => $value;
        }
    }
}
