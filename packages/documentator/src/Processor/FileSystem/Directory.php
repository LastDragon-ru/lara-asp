<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Core\Path\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;

use function sprintf;

/**
 * @extends Entry<DirectoryPath>
 */
class Directory extends Entry {
    public function __construct(FileSystemAdapter $adapter, DirectoryPath $path) {
        parent::__construct($adapter, $path);

        if (!$this->adapter->isDirectory($this->path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `%s` is not a directory.',
                    $this->path,
                ),
            );
        }
    }

    public function isInside(self|DirectoryPath|FilePath|File $path): bool {
        return $this->path->isInside(
            $path instanceof Path ? $path : $path->path,
        );
    }
}
