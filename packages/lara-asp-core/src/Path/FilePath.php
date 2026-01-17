<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Path;

use LastDragon_ru\LaraASP\Core\Package;
use Override;

use function dirname;
use function pathinfo;
use function trigger_deprecation;

use const PATHINFO_EXTENSION;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '10.0.0', 'The `\LastDragon_ru\Path\FilePath` from `lastdragon-ru/path` package should be used instead.');

/**
 * @deprecated 10.0.0 The `\LastDragon_ru\Path\FilePath` from `lastdragon-ru/path` package should be used instead.
 */
class FilePath extends Path {
    #[Override]
    public function getParentPath(): DirectoryPath {
        return $this->getDirectory();
    }

    #[Override]
    protected function getDirectory(): DirectoryPath {
        return new DirectoryPath(dirname($this->path));
    }

    /**
     * @return ?non-empty-string
     */
    public function getExtension(): ?string {
        $extension = pathinfo($this->path, PATHINFO_EXTENSION);
        $extension = $extension !== '' ? $extension : null;

        return $extension;
    }
}
