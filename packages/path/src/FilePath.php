<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use Override;

use function pathinfo;

use const PATHINFO_EXTENSION;

/**
 * @property-read ?non-empty-string $extension
 */
class FilePath extends Path {
    /**
     * @deprecated %{VERSION} Will be replaced to property hooks soon.
     */
    #[Override]
    public function __get(string $name): mixed {
        return match ($name) {
            'extension' => $this->extension(),
            default     => parent::__get($name),
        };
    }

    /**
     * @return ?non-empty-string
     */
    private function extension(): ?string {
        $extension = pathinfo($this->path, PATHINFO_EXTENSION);
        $extension = $extension !== '' ? $extension : null;

        return $extension;
    }
}
