<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use Override;

use function str_ends_with;
use function str_starts_with;

class DirectoryPath extends Path {
    public function contains(Path $path): bool {
        $path   = (string) $this->resolve($path);
        $root   = (string) $this->normalized();
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
