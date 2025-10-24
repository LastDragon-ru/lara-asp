<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;

use function sprintf;

/**
 * @internal
 */
class FileReal extends File {
    public function __construct(FileSystemAdapter $adapter, FilePath $path, Caster $caster) {
        parent::__construct($adapter, $path, $caster);

        if (!$this->adapter->isFile((string) $this->path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `%s` is not a file.',
                    $this->path,
                ),
            );
        }
    }
}
