<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

use function file_get_contents;

/**
 * @implements Metadata<string>
 */
readonly class Content implements Metadata {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(File $file): mixed {
        return (string) file_get_contents((string) $file);
    }
}
