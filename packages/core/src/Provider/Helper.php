<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use ReflectionClass;

use function dirname;
use function ltrim;

trait Helper {
    /**
     * Should return the name of the package.
     */
    abstract protected function getName(): string;

    /**
     * Returns path relative to class location.
     */
    protected function getPath(string $path): string {
        $class = new ReflectionClass(self::class);
        $path  = dirname((string) $class->getFileName()).'/'.ltrim($path, '/');

        return $path;
    }
}
