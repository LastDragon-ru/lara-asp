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
readonly class ExecutorTraversable implements IteratorAggregate {
    public function __construct(
        /**
         * @var Dependency<*>
         */
        private Dependency $dependency,
        /**
         * @var Traversable<TKey, TValue>
         */
        private Traversable $resolved,
        /**
         * @var Closure(Dependency<*>, TValue): mixed
         */
        private Closure $handler,
    ) {
        // empty
    }

    /**
     * @return Generator<TKey, TValue>
     */
    #[Override]
    public function getIterator(): Generator {
        foreach ($this->resolved as $key => $value) {
            ($this->handler)($this->dependency, $value);

            yield $key => $value;
        }
    }
}
