<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LogicException;
use Override;

/**
 * @internal
 */
class FileVirtual extends File {
    #[Override]
    public function getContent(): string {
        throw new LogicException('Virtual cannot have content.');
    }
}
