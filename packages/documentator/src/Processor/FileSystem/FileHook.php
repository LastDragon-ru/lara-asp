<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;

use function array_map;
use function in_array;
use function sprintf;

/**
 * @internal
 */
class FileHook extends File {
    public function __construct(Metadata $metadata, FilePath $path) {
        parent::__construct($metadata, $path);

        $extensions = array_map(static fn ($hook) => $hook->value, Hook::cases());
        $extension  = $path->getExtension();

        if (!in_array($extension, $extensions, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `%s` is not a hook.',
                    $this->path,
                ),
            );
        }
    }
}
