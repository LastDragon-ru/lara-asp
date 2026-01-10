<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use IteratorAggregate;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use Override;
use Traversable;

use function array_pop;
use function count;

/**
 * @internal
 * @implements IteratorAggregate<mixed, FilePath>
 */
class Iterator implements IteratorAggregate {
    /**
     * @var array<string, FilePath>
     */
    private array $queue = [];

    public function __construct(
        /**
         * @var iterable<mixed, DirectoryPath|FilePath>
         */
        private readonly iterable $files,
    ) {
        // empty
    }

    #[Override]
    public function getIterator(): Traversable {
        foreach ($this->files as $path) {
            if ($path instanceof DirectoryPath) {
                continue;
            }

            yield $path;
            yield from $this->queue();
        }

        yield from $this->queue();
    }

    public function push(FilePath $path): void {
        $this->queue[$path->path] = $path;
    }

    /**
     * @return iterable<mixed, FilePath>
     */
    private function queue(): iterable {
        while (count($this->queue) > 0) {
            yield array_pop($this->queue);
        }

        yield from [];
    }
}
