<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use Override;

use function str_ends_with;
use function str_starts_with;

/**
 * @extends Path<string>
 */
final class DirectoryPath extends Path {
    #[Override]
    public function directory(?string $path = null): self {
        return $path !== null ? parent::directory($path) : $this->normalized();
    }

    public function contains(Path $path): bool {
        $path   = (string) $this->resolve($path);
        $root   = (string) $this->normalized();
        $inside = $path !== $root && str_starts_with($path, $root);

        return $inside;
    }

    /**
     * @inheritDoc
     *
     * @return non-empty-string
     */
    #[Override]
    protected static function normalize(Type $type, array $parts): string {
        $normalized = parent::normalize($type, $parts);
        $normalized = $normalized !== '' ? $normalized : '.';
        $normalized = $normalized.(str_ends_with($normalized, '/') ? '' : '/');

        return $normalized;
    }
}
