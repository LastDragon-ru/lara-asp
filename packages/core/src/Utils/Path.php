<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use Symfony\Component\Filesystem\Path as SymfonyPath;

use function dirname;
use function is_file;

class Path {
    public static function getPath(string $root, string $path): string {
        $path = static::isRelative($path)
            ? static::join(static::getDirname($root), $path)
            : $path;
        $path = static::normalize($path);

        return $path;
    }

    public static function getRelativePath(string $root, string $path): string {
        $root = static::getDirname($root);
        $path = static::isRelative($path) ? static::join($root, $path) : $path;
        $path = SymfonyPath::makeRelative($path, $root);
        $path = static::normalize($path);

        return $path;
    }

    public static function getDirname(string $path): string {
        return static::normalize(is_file($path) ? dirname($path) : $path);
    }

    public static function isRelative(string $path): bool {
        return SymfonyPath::isRelative(static::normalize($path));
    }

    public static function isAbsolute(string $path): bool {
        return SymfonyPath::isAbsolute(static::normalize($path));
    }

    public static function isNormalized(string $path): bool {
        return static::normalize($path) === $path;
    }

    public static function normalize(string $path): string {
        return SymfonyPath::canonicalize($path);
    }

    public static function join(string ...$paths): string {
        return SymfonyPath::join(...$paths);
    }
}
