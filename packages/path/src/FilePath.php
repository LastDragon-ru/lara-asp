<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use Override;

use function dirname;
use function pathinfo;

use const PATHINFO_EXTENSION;

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
