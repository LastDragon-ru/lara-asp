<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Symfony\Component\Filesystem\Path as SymfonyPath;

use function dirname;
use function is_file;

class Path {
    public static function getPath(string $root, string $path): string {
        $root = is_file($root) ? dirname($root) : $root;
        $path = static::isAbsolute($path) ? $path : "{$root}/{$path}";
        $path = static::normalize($path);

        return $path;
    }

    public static function isAbsolute(string $path): bool {
        return SymfonyPath::isAbsolute($path);
    }

    public static function normalize(string $path): string {
        return SymfonyPath::canonicalize($path);
    }
}
