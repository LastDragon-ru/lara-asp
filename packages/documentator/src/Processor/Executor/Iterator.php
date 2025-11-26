<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
use Override;
use Traversable;

use function array_shift;

/**
 * @internal
 * @implements IteratorAggregate<mixed, File>
 */
class Iterator implements IteratorAggregate {
    /**
     * @var array<mixed, FilePath>
     */
    private array $files = [];

    public function __construct(
        private readonly FileSystem $fs,
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

        while ($this->files !== []) {
            $file = array_shift($this->files);
            $file = $this->fs->getFile($file);

            yield $file;
        }
    }

    public function push(FilePath $file): void {
        $this->files[] = $file;
    }
}
