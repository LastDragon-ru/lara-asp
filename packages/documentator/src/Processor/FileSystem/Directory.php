<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\Path;

use function is_dir;
use function sprintf;

/**
 * @extends Item<DirectoryPath>
 */
class Directory extends Item {
    public function __construct(DirectoryPath $path) {
        parent::__construct($path);

        if (!is_dir((string) $this->path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `%s` is not a directory.',
                    $this->path,
                ),
            );
        }
    }

    public function isInside(self|File|Path $path): bool {
        return $this->path->isInside(
            $path instanceof Path ? $path : $path->path,
        );
    }
}
