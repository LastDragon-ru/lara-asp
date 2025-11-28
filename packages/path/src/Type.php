<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

enum Type: string {
    /**
     * Universal Naming Convention (UNC, uniform naming convention, or network path)
     *
     * @see https://en.wikipedia.org/wiki/Path_(computing)#UNC
     */
    case Unc = '//';
    /**
     * Inside user home directory (`~/...`).
     */
    case Home     = '~/';
    case Absolute = '/';
    case Relative = '';
}
