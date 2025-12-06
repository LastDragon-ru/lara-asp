<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
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
    private array $queue = [];

    public function __construct(
        private readonly FileSystem $fs,
        /**
         * @var iterable<mixed, FilePath>
         */
        private readonly iterable $files,
    ) {
        // empty
    }

    #[Override]
    public function getIterator(): Traversable {
        foreach ($this->files as $path) {
            yield $this->fs->get($path);
        }

        while (count($this->queue) > 0) {
            yield array_pop($this->queue);
        }
    }

    public function push(File $file): void {
        $this->queue[] = $file;
    }
}
