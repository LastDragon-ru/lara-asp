<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Symfony\Component\Filesystem\Path as SymfonyPath;

use function dirname;
use function is_file;

class Path {
    public static function getPath(string $root, string $path): string {
        $path = static::isRelative($path)
            ? SymfonyPath::join(static::getDirname($root), $path)
            : $path;
        $path = static::normalize($path);

        return $path;
    }

    public static function getDirname(string $path): string {
        return is_file($path) ? dirname($path) : $path;
    }

    public static function isRelative(string $path): bool {
        return SymfonyPath::isRelative($path);
    }

    public static function isAbsolute(string $path): bool {
        return SymfonyPath::isAbsolute($path);
    }

    public static function normalize(string $path): string {
        return SymfonyPath::canonicalize($path);
    }
}
