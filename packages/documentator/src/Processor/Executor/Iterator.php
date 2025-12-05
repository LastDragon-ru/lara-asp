<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;
use Traversable;

use function array_pop;
use function count;

/**
 * @internal
 * @implements IteratorAggregate<mixed, File>
 */
class Iterator implements IteratorAggregate {
    /**
     * @var array<mixed, File>
     */
    private array $files = [];

    public function __construct(
        /**
         * @var iterable<mixed, File>
         */
        private readonly iterable $iterator,
    ) {
        // empty
    }

    #[Override]
    public function getIterator(): Traversable {
        yield from [];
        yield from $this->iterator;

        while (count($this->files) > 0) {
            yield array_pop($this->files);
        }
    }

    public function push(File $file): void {
        $this->files[] = $file;
    }
}
