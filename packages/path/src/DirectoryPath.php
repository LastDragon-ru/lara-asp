<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use Override;

use function str_ends_with;
use function str_starts_with;

class DirectoryPath extends Path {
    #[Override]
    public function getParentPath(): self {
        return $this->getDirectoryPath('..');
    }

    #[Override]
    protected function getDirectory(): self {
        return $this;
    }

    public function isInside(Path $path): bool {
        $path   = (string) $this->getPath($path);
        $root   = (string) $this->getNormalizedPath();
        $inside = $path !== $root && str_starts_with($path, $root);

        return $inside;
    }

    #[Override]
    protected function normalize(string $path): string {
        $normalized = parent::normalize($path);
        $normalized = $normalized !== '' ? $normalized : '.';
        $normalized = $normalized.(str_ends_with($normalized, '/') ? '' : '/');

        return $normalized;
    }
}
