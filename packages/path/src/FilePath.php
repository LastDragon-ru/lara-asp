<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use InvalidArgumentException;
use Override;

use function pathinfo;
use function str_ends_with;

use const PATHINFO_EXTENSION;

/**
 * @property-read non-empty-string  $name
 * @property-read ?non-empty-string $extension
 *
 * @extends Path<non-empty-string>
 */
final class FilePath extends Path {
    /**
     * @param non-empty-string $path
     */
    public function __construct(string $path) {
        parent::__construct($path);

        if ($this->name === '.' || $this->name === '..') {
            throw new InvalidArgumentException('Filename cannot be `.` or `..`.');
        } elseif ($this->name === '' || !str_ends_with($path, $this->name)) { // @phpstan-ignore identical.alwaysFalse
            throw new InvalidArgumentException('Filename cannot be empty.');
        } else {
            // empty
        }
    }

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
