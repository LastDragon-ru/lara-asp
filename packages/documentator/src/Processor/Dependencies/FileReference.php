<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Dependencies;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Override;

use function is_string;

/**
 * @implements Dependency<File>
 */
class FileReference implements Dependency {
    public function __construct(
        protected readonly FilePath|string $reference,
    ) {
        // empty
    }

    #[Override]
    public function __invoke(FileSystem $fs): mixed {
        // Create
        $resolved = $fs->getFile($fs->input->getPath($this->getPath()));

        if ($resolved === null) {
            throw new DependencyUnresolvable($this);
        }

        return $resolved;
    }

    #[Override]
    public function getPath(): FilePath {
        return match (true) {
            is_string($this->reference) => new FilePath($this->reference),
            default                     => $this->reference,
        };
    }
}
