<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Path;

use Override;

class DirectoryPath extends Path {
    #[Override]
    public function getParentPath(): self {
        return $this->getDirectoryPath('..');
    }

    #[Override]
    protected function getDirectory(): self {
        return $this;
    }
}
