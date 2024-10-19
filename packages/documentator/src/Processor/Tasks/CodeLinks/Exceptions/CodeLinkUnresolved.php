<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Exceptions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Throwable;

use function implode;
use function sprintf;

class CodeLinkUnresolved extends CodeLinkError {
    public function __construct(
        public readonly Directory $root,
        protected readonly File $target,
        /**
         * @var non-empty-list<string>
         */
        public array $unresolved,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The following unresolved code links have been found in `%2$s`: %1$s (root: `%3$s`)',
                '`'.implode('`, `', $this->unresolved).'`',
                $this->root->getRelativePath($this->target),
                $this->root->getPath(),
            ),
            $previous,
        );
    }

    public function getRoot(): Directory {
        return $this->root;
    }

    public function getTarget(): File {
        return $this->target;
    }

    /**
     * @return non-empty-list<string>
     */
    public function getUnresolved(): array {
        return $this->unresolved;
    }
}
