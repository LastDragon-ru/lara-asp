<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

enum Type {
    /**
     * `/path`
     */
    case Absolute;
    /**
     * `path`, `./path`, `../path`
     */
    case Relative;
    /**
     * Universal Naming Convention (UNC, uniform naming convention, or network path)
     *
     * @see https://en.wikipedia.org/wiki/Path_(computing)#UNC
     */
    case Unc;
    /**
     * User home directory path (`~/path`).
     */
    case Home;
    /**
     * `C:\\path`
     */
    case WindowsAbsolute;
    /**
     * `C:path` (resolves relative to the current directory of the `C` drive)
     *
     * @see https://learn.microsoft.com/en-us/dotnet/standard/io/file-path-formats
     */
    case WindowsRelative;
}
