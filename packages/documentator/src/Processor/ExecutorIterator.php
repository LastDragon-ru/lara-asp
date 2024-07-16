<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use Generator;
use Iterator;
use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

/**
 * @internal
 *
 * @template TKey
 * @template TValue of Directory|File
 *
 * @implements IteratorAggregate<TKey, TValue>
 */
class ExecutorIterator implements IteratorAggregate {
    private float $duration = 0;

    public function __construct(
        /**
         * @var Dependency<Iterator<TKey, TValue>>
         */
        private readonly Dependency $dependency,
        /**
         * @var Iterator<TKey, TValue>
         */
        private readonly Iterator $resolved,
        /**
         * @var Closure(Dependency<Iterator<TKey, TValue>>, TValue): float
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
